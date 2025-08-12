<div class="text-center completion-screen">
    <div class="trophy-animation mb-4">
        <div class="trophy">🏆</div>
    </div>
    
    <h1 class="display-4 mb-4 fade-in-text">Session Complete!</h1>
    
    <div class="mb-4 fade-in-delayed">
        <p class="fs-5">Excellent work! You've mastered all scales for this session.</p>
    </div>
    
    <div class="row justify-content-center mb-4 fade-in-delayed-more">
        <div class="col-auto">
            <div class="card bg-success text-white stats-card">
                <div class="card-body">
                    <h5>🎯 Final Stats</h5>
                    <div class="stat-row">
                        <span>Total Attempts:</span>
                        <span class="stat-value"><?= $stats['overall']['attempt_no'] ?></span>
                    </div>
                    <div class="stat-row">
                        <span>Successes:</span>
                        <span class="stat-value"><?= $stats['overall']['total_successes'] ?></span>
                    </div>
                    <div class="stat-row">
                        <span>Failures:</span>
                        <span class="stat-value"><?= $stats['overall']['total_failures'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <button class="btn btn-primary btn-lg pulse-button" 
            hx-post="/reset-session" 
            hx-target="body">
        Start New Session
    </button>
</div>

<style>
    .completion-screen {
        animation: slideUp 0.5s ease-out;
    }
    
    .trophy-animation {
        display: inline-block;
        animation: bounce 1s ease-in-out;
    }
    
    .trophy {
        font-size: 5rem;
        animation: rotate 2s ease-in-out;
    }
    
    .fade-in-text {
        animation: fadeIn 0.8s ease-out 0.3s both;
    }
    
    .fade-in-delayed {
        animation: fadeIn 0.8s ease-out 0.6s both;
    }
    
    .fade-in-delayed-more {
        animation: fadeIn 0.8s ease-out 0.9s both;
    }
    
    .stats-card {
        animation: scaleIn 0.5s ease-out 1s both;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    .stat-row {
        display: flex;
        justify-content: space-between;
        padding: 4px 0;
    }
    
    .stat-value {
        font-weight: bold;
    }
    
    .pulse-button {
        animation: pulse 2s infinite 1.5s;
    }
    
    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    @keyframes rotate {
        0% { transform: rotate(0deg) scale(0.5); }
        50% { transform: rotate(180deg) scale(1.2); }
        100% { transform: rotate(360deg) scale(1); }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.8);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
    
    @keyframes pulse {
        0%, 100% { 
            transform: scale(1); 
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        50% { 
            transform: scale(1.05); 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
    }
</style>

<script>
    // Multiple confetti bursts for celebration
    const duration = 3 * 1000;
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    const interval = setInterval(function() {
        const timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        const particleCount = 50 * (timeLeft / duration);
        
        // Confetti from left
        confetti(Object.assign({}, defaults, {
            particleCount,
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
        }));
        
        // Confetti from right
        confetti(Object.assign({}, defaults, {
            particleCount,
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
        }));
    }, 250);
    
    // Big center burst
    setTimeout(() => {
        confetti({
            particleCount: 150,
            spread: 100,
            origin: { y: 0.4 }
        });
    }, 500);
</script>