<div class="row text-center mb-3">
    <div class="col-6 col-md-3 mb-2">
        <div class="badge bg-info fs-6 w-100 p-2">
            <div>Attempt #</div>
            <div class="fs-4"><?= $stats['overall']['attempt_no'] + 1 ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="badge bg-success fs-6 w-100 p-2">
            <div>Completed</div>
            <div class="fs-4">
                <?= $stats['overall']['completed_scales'] ?? 0 ?>/<?= $stats['overall']['total_scales'] ?? 0 ?>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="badge bg-primary fs-6 w-100 p-2">
            <div>Successes</div>
            <div class="fs-4"><?= $stats['overall']['total_successes'] ?? 0 ?></div>
        </div>
    </div>
    <div class="col-6 col-md-3 mb-2">
        <div class="badge bg-warning fs-6 w-100 p-2">
            <div>Failures</div>
            <div class="fs-4"><?= $stats['overall']['total_failures'] ?? 0 ?></div>
        </div>
    </div>
</div>