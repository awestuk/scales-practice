<div class="card scale-card">
    <div class="card-body text-center p-5">
        <h1 class="display-3 mb-4 scale-name" data-scale-id="<?= $scale['scale_id'] ?>">
            <?= htmlspecialchars($scale['name']) ?>
        </h1>
        
        <?php if ($showNotes && !empty($scale['notes'])): ?>
            <p class="text-muted fs-5 mb-4"><?= htmlspecialchars($scale['notes']) ?></p>
        <?php endif; ?>
        
        <div class="mb-4">
            <span class="badge bg-secondary fs-6">
                Tokens: <?= $scale['tokens_remaining'] ?>
            </span>
        </div>
        
        <div class="outcome-buttons">
            <p class="text-muted mb-3">How did you do?</p>
            
            <div class="d-grid gap-2 d-md-flex justify-content-md-center">
                <button class="btn btn-success btn-lg px-4 py-3" 
                        hx-post="/attempt" 
                        hx-vals='{"scale_id": "<?= $scale['scale_id'] ?>", "outcome": "success"}'
                        hx-target="#main-content" 
                        hx-swap="innerHTML">
                    <span class="fs-4">✓ Nailed It!</span>
                </button>
                
                <button class="btn btn-danger btn-lg px-4 py-3" 
                        hx-post="/attempt" 
                        hx-vals='{"scale_id": "<?= $scale['scale_id'] ?>", "outcome": "fail"}'
                        hx-target="#main-content" 
                        hx-swap="innerHTML">
                    <span class="fs-4">✗ Missed</span>
                </button>
            </div>
            
            <div class="mt-3 text-muted">
                <small>Press <kbd>Y</kbd> for success or <kbd>N</kbd> for fail</small>
            </div>
        </div>
    </div>
</div>