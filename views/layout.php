<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Piano Scale Reps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/app.css">
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary mb-2 mb-md-3">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">Piano Scale Reps</a>
            <div class="d-flex align-items-center">
                <a href="/settings" class="btn btn-outline-light me-2">Settings</a>
                <?php if ($session->session_date < date('Y-m-d')): ?>
                    <button class="btn btn-warning me-2"
                            hx-post="/new-day"
                            hx-target="body">
                        Start New Day
                    </button>
                <?php endif; ?>
                <?php if ($isLoggedIn): ?>
                    <span class="text-light me-2 d-none d-md-inline"><?= htmlspecialchars($user['email']) ?></span>
                    <a href="/logout" class="btn btn-outline-light btn-sm">Logout</a>
                <?php else: ?>
                    <a href="/login" class="btn btn-outline-light btn-sm">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <!-- Stats Badges -->
        <div id="stats-badges"
             hx-get="/stats-badges"
             hx-trigger="load, every 2s">
            <?php include __DIR__ . '/fragments/stats-badges.php'; ?>
        </div>
        
        <!-- Main Content Area -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-8 col-lg-6">
                <div id="main-content">
                    <?php include __DIR__ . '/home.php'; ?>
                </div>
            </div>
        </div>
        
        <!-- Scale Progress -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-10 col-lg-8">
                <div id="scale-progress" 
                     hx-get="/scale-progress" 
                     hx-trigger="load, every 2s">
                    <?php include __DIR__ . '/fragments/scale-progress.php'; ?>
                </div>
            </div>
        </div>
        
        <!-- Session Controls -->
        <div class="row justify-content-center mt-4 mb-5">
            <div class="col-md-6 col-lg-4 text-center">
                <button class="btn btn-danger" 
                        hx-post="/reset-session" 
                        hx-confirm="Are you sure you want to reset this session?"
                        hx-target="body">
                    Reset Session
                </button>
            </div>
        </div>
        
        <!-- Version footer -->
        <div class="text-center text-muted mt-5 mb-3">
            <small>Version 1.3.0</small>
        </div>
    </div>
    
    <!-- CSRF for HTMX -->
    <meta name="csrf-name" content="<?= htmlspecialchars($csrfNameValue) ?>">
    <meta name="csrf-value" content="<?= htmlspecialchars($csrfTokenValue) ?>">
    <script>
        document.body.addEventListener('htmx:configRequest', (event) => {
            const csrfName = document.querySelector('meta[name="csrf-name"]').content;
            const csrfValue = document.querySelector('meta[name="csrf-value"]').content;
            event.detail.parameters['csrf_name'] = csrfName;
            event.detail.parameters['csrf_value'] = csrfValue;
        });
    </script>
    
    <script src="/assets/app.js"></script>
</body>
</html>