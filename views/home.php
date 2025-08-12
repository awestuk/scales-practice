<div class="card scale-card">
    <div class="card-body text-center p-5">
        <?php if ($this->statsService->isSessionComplete($session->id)): ?>
            <?php include __DIR__ . '/fragments/complete.php'; ?>
        <?php else: ?>
            <h2 class="mb-4">Ready to Practice!</h2>
            <p class="text-muted mb-4">Tap the button to get your next scale</p>
            
            <button class="btn btn-primary btn-lg px-5 py-3" 
                    hx-post="/next-scale" 
                    hx-target="#main-content" 
                    hx-swap="innerHTML">
                <span class="fs-4">Show Next Scale</span>
            </button>
            
            <div class="mt-4 text-muted">
                <small>Press <kbd>Space</kbd> to advance</small>
            </div>
        <?php endif; ?>
    </div>
</div>