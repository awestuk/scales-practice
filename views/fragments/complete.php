<div class="text-center">
    <h1 class="display-4 mb-4">🎉 Session Complete!</h1>
    
    <div class="mb-4">
        <p class="fs-5">Great job! You've completed all scales for this session.</p>
    </div>
    
    <div class="row justify-content-center mb-4">
        <div class="col-auto">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5>Final Stats</h5>
                    <p class="mb-1">Total Attempts: <?= $stats['overall']['attempt_no'] ?></p>
                    <p class="mb-1">Successes: <?= $stats['overall']['total_successes'] ?></p>
                    <p class="mb-0">Failures: <?= $stats['overall']['total_failures'] ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <button class="btn btn-primary btn-lg" 
            hx-post="/reset-session" 
            hx-target="body">
        Start New Session
    </button>
</div>

<script>
    // Trigger confetti
    confetti({
        particleCount: 100,
        spread: 70,
        origin: { y: 0.6 }
    });
</script>