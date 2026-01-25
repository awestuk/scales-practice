<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Scale Progress</h5>
    </div>
    <div class="card-body">
        <div class="row g-2">
            <?php foreach ($stats['scales'] as $scale): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="scale-progress-item">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-truncate me-2">
                                <?= htmlspecialchars($scale['scale_name']) ?>
                                <span class="badge bg-secondary" style="font-size: 0.65em;"><?= htmlspecialchars($scale['scale_type'] ?? 'Other') ?></span>
                            </small>
                            <small class="text-muted">
                                <?= $scale['successes'] ?>✓ <?= $scale['failures'] ?>✗
                            </small>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <?php 
                            $maxTokens = $session->required_successes;
                            $completed = $maxTokens - $scale['tokens_remaining'];
                            $percentage = ($completed / $maxTokens) * 100;
                            ?>
                            <div class="progress-bar <?= $percentage == 100 ? 'bg-success' : 'bg-primary' ?>" 
                                 style="width: <?= $percentage ?>%">
                                <?= $completed ?>/<?= $maxTokens ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>