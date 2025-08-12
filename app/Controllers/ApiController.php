<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Container\ContainerInterface;
use App\Domain\SessionService;
use App\Domain\StatsService;
use App\Domain\Scheduler;
use App\Models\Scale;
use App\Models\Attempt;
use App\Models\Session;

class ApiController
{
    private SessionService $sessionService;
    private StatsService $statsService;
    private Scheduler $scheduler;
    private ContainerInterface $container;
    
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->sessionService = new SessionService();
        $this->statsService = new StatsService();
        $this->scheduler = new Scheduler();
    }
    
    public function health(Request $request, Response $response): Response
    {
        $data = ['status' => 'healthy', 'timestamp' => date('c')];
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }
    
    public function nextScale(Request $request, Response $response): Response
    {
        $session = $this->sessionService->getOrCreateActiveSession();
        
        // Get previous scale ID from session/cookies if exists
        $cookies = $request->getCookieParams();
        $prevScaleId = isset($cookies['last_scale_id']) ? (int)$cookies['last_scale_id'] : null;
        
        // Get next scale
        $scale = $this->scheduler->nextScale($session->id, $prevScaleId);
        
        if (!$scale) {
            // Session complete
            $stats = $this->statsService->getSessionStats($session->id);
            ob_start();
            include dirname(__DIR__, 2) . '/views/fragments/complete.php';
            $html = ob_get_clean();
        } else {
            // Update attempt number and last shown
            $attemptNo = $session->getNextAttemptNo();
            $this->scheduler->updateLastShown($session->id, $scale['scale_id'], $attemptNo);
            
            // Get show_notes setting
            $showNotes = $this->sessionService->getShowNotes();
            
            // Render scale card
            ob_start();
            include dirname(__DIR__, 2) . '/views/fragments/scale-card.php';
            $html = ob_get_clean();
            
            // Set cookie for last scale
            setcookie('last_scale_id', $scale['scale_id'], [
                'expires' => time() + 86400,
                'path' => '/',
                'samesite' => 'Strict'
            ]);
        }
        
        $response->getBody()->write($html);
        return $response;
    }
    
    public function attempt(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $scaleId = (int)($data['scale_id'] ?? 0);
        $outcome = $data['outcome'] ?? '';
        
        if (!in_array($outcome, ['success', 'fail'])) {
            $response->getBody()->write('<div class="alert alert-danger">Invalid outcome</div>');
            return $response->withStatus(400);
        }
        
        $session = $this->sessionService->getOrCreateActiveSession();
        $attemptNo = $session->getNextAttemptNo();
        
        // Record attempt
        Attempt::record($session->id, $scaleId, $attemptNo, $outcome);
        
        // Update scale state
        $this->scheduler->recordOutcome($session->id, $scaleId, $outcome, $session->required_successes);
        
        // Return next scale
        return $this->nextScale($request, $response);
    }
    
    public function resetSession(Request $request, Response $response): Response
    {
        $this->sessionService->resetSession();
        
        // Return refreshed home view
        $response->getBody()->write('<div hx-get="/" hx-trigger="load" hx-target="body"></div>');
        return $response;
    }
    
    public function newDay(Request $request, Response $response): Response
    {
        if (!$this->sessionService->canStartNewDay()) {
            $response->getBody()->write('<div class="alert alert-warning">A session already exists for today</div>');
            return $response;
        }
        
        $this->sessionService->getOrCreateActiveSession();
        
        // Return refreshed home view
        $response->getBody()->write('<div hx-get="/" hx-trigger="load" hx-target="body"></div>');
        return $response;
    }
    
    public function statsBadges(Request $request, Response $response): Response
    {
        $session = $this->sessionService->getOrCreateActiveSession();
        $stats = $this->statsService->getSessionStats($session->id);
        
        ob_start();
        include dirname(__DIR__, 2) . '/views/fragments/stats-badges.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }
    
    public function scaleProgress(Request $request, Response $response): Response
    {
        $session = $this->sessionService->getOrCreateActiveSession();
        $stats = $this->statsService->getSessionStats($session->id);
        
        ob_start();
        include dirname(__DIR__, 2) . '/views/fragments/scale-progress.php';
        $html = ob_get_clean();
        
        $response->getBody()->write($html);
        return $response;
    }
    
    public function saveSettings(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        
        // Update required_successes
        $value = 3; // default
        if (isset($data['required_successes'])) {
            $value = max(1, min(10, (int)$data['required_successes']));
            $this->sessionService->updateConfig('required_successes', (string)$value);
        }
        
        // Update allow_repeat
        $allowRepeat = isset($data['allow_repeat']) ? '1' : '0';
        $this->sessionService->updateConfig('allow_repeat_when_last_only', $allowRepeat);
        
        // Update show_notes
        $showNotes = isset($data['show_notes']) ? '1' : '0';
        $this->sessionService->updateConfig('show_notes', $showNotes);
        
        // Reseed active session if required_successes changed
        $session = $this->sessionService->getOrCreateActiveSession();
        if ($session && $session->required_successes != $value) {
            $this->sessionService->resetSession();
        }
        
        $response->getBody()->write('<div class="alert alert-success">Settings saved successfully</div>');
        return $response;
    }
    
    public function addScale(Request $request, Response $response): Response
    {
        $data = $request->getParsedBody();
        $name = trim($data['name'] ?? '');
        $notes = trim($data['notes'] ?? '');
        
        if (empty($name)) {
            $response->getBody()->write('<div class="alert alert-danger">Scale name is required</div>');
            return $response->withStatus(400);
        }
        
        try {
            Scale::create($name, $notes);
            
            // Reseed active session with new scale
            $scales = Scale::findAll();
            $scaleIds = array_map(fn($s) => $s->id, $scales);
            $this->sessionService->reseedActiveSession($scaleIds);
            
            // Return updated settings view
            return $this->settings($request, $response);
        } catch (\Exception $e) {
            $response->getBody()->write('<div class="alert alert-danger">Scale already exists</div>');
            return $response->withStatus(400);
        }
    }
    
    public function deleteScale(Request $request, Response $response, array $args): Response
    {
        $scaleId = (int)$args['id'];
        $scale = Scale::find($scaleId);
        
        if ($scale) {
            $scale->delete();
            
            // Reseed active session without deleted scale
            $scales = Scale::findAll();
            $scaleIds = array_map(fn($s) => $s->id, $scales);
            $this->sessionService->reseedActiveSession($scaleIds);
        }
        
        // Return updated settings view
        return $this->settings($request, $response);
    }
    
    private function settings(Request $request, Response $response): Response
    {
        $controller = new UiController($this->container);
        return $controller->settings($request, $response);
    }
}