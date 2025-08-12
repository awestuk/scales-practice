<div class="card scale-card">
    <div class="card-body text-center p-5">
        <?php if ($this->statsService->isSessionComplete($session->id)): ?>
            <?php include __DIR__ . '/fragments/complete.php'; ?>
        <?php else: ?>
            <h2 class="mb-3">Ready to Practice!</h2>
            
            <div class="alert alert-info text-start mb-4" style="max-width: 500px; margin: 0 auto;">
                <h6 class="alert-heading">How it works:</h6>
                <ul class="mb-0 small">
                    <li>Each scale needs <strong><?= $session->required_successes ?> first-try successes</strong> to complete</li>
                    <li>✗ Failure resets the scale back to <?= $session->required_successes ?> goals</li>
                    <li>Complete all scales to finish your session!</li>
                </ul>
            </div>
            
            <p class="text-muted mb-4">Tap the button to get your next scale</p>
            
            <button class="btn btn-primary btn-lg px-5 py-3" 
                    hx-post="/next-scale" 
                    hx-target="#main-content" 
                    hx-swap="innerHTML">
                <span class="fs-4">Show Next Scale</span>
            </button>
            
            <div class="mt-4 text-muted">
                <small>Keyboard: <kbd>Space</kbd> to advance, <kbd>Y</kbd> for success, <kbd>N</kbd> for fail</small>
            </div>
        <?php endif; ?>
    </div>
</div>