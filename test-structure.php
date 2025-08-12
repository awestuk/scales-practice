#!/usr/bin/env php
<?php
echo "Testing Piano Scale Reps structure...\n\n";

$required_files = [
    'composer.json',
    'Dockerfile',
    'fly.toml',
    '.dockerignore',
    'README.md',
    'plan.md',
    'public/index.php',
    'public/.htaccess',
    'public/assets/app.css',
    'public/assets/app.js',
    'app/Router.php',
    'app/Controllers/UiController.php',
    'app/Controllers/ApiController.php',
    'app/Domain/Scheduler.php',
    'app/Domain/SessionService.php',
    'app/Domain/StatsService.php',
    'app/Storage/Db.php',
    'app/Storage/Migrations.php',
    'app/Models/Scale.php',
    'app/Models/Session.php',
    'app/Models/Attempt.php',
    'views/layout.php',
    'views/home.php',
    'views/settings.php',
    'views/fragments/stats-badges.php',
    'views/fragments/scale-progress.php',
    'views/fragments/scale-card.php',
    'views/fragments/complete.php',
    'bin/migrate.php',
    'data/.gitignore'
];

$missing = [];
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✓ $file\n";
    } else {
        echo "✗ $file (MISSING)\n";
        $missing[] = $file;
    }
}

echo "\n";
if (empty($missing)) {
    echo "SUCCESS: All required files are present!\n";
    echo "\nNext steps:\n";
    echo "1. Run: composer install\n";
    echo "2. Run: php bin/migrate.php\n";
    echo "3. Run: php -S 0.0.0.0:8081 -t public\n";
    echo "4. Open: http://localhost:8081\n";
} else {
    echo "ERROR: " . count($missing) . " files are missing.\n";
    exit(1);
}