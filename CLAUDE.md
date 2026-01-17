# Piano Scale Reps - Claude Code Context

## Overview
A touch-friendly web app for mastering piano scales through intelligent spaced repetition with a streak-based system. Users practice scales, mark success/failure, and the app intelligently selects the next scale using weighted random selection.

## Tech Stack
- **Backend:** PHP 8.2+, Slim Framework 4, PDO SQLite, League OAuth2 Google
- **Frontend:** Bootstrap 5.3, HTMX 1.9.10 (dynamic updates without page reloads), vanilla JS
- **Database:** SQLite with WAL mode
- **Deployment:** Docker, Fly.io, Apache

## Project Structure
```
/public/
  index.php              # Entry point
  /assets/
    app.js               # Keyboard shortcuts & HTMX integration
    app.css              # Bootstrap customizations

/app/
  Router.php             # Route configuration & CSRF middleware
  /Controllers/
    ApiController.php    # API endpoints (next-scale, record-attempt)
    UiController.php     # Page rendering (home, settings)
    AuthController.php   # Google OAuth flow
  /Domain/
    Scheduler.php        # Weighted random scale selection algorithm
    SessionService.php   # Session & config management
    AuthService.php      # Authentication & authorization
    StatsService.php     # Statistics aggregation
  /Models/
    Session.php, Scale.php, Attempt.php  # Data access objects
  /Storage/
    Db.php               # SQLite PDO singleton
    Migration.php        # Migration base class
    MigrationRunner.php  # Automated migration system

/views/
  layout.php             # Main page layout
  home.php               # Practice interface
  settings.php           # Configuration & scale management
  login.php              # Google OAuth login page
  /fragments/            # HTMX partial templates
    scale-card.php, scale-progress.php, stats-badges.php, complete.php

/migrations/             # Numbered migration files (001_, 002_, etc.)
/bin/
  migrate.php            # Migration CLI runner
/data/
  app.db                 # SQLite database (git-ignored)
```

## Key Commands
```bash
# Install dependencies
composer install

# Run migrations
php bin/migrate.php

# Start development server
php -S 0.0.0.0:8081 -t public

# Run tests
./vendor/bin/phpunit

# Docker build & run
docker build -t piano-scale-reps .
docker run -p 8081:8081 piano-scale-reps
```

## Database Schema
- **scales:** id, name (unique), notes
- **sessions:** id, browser_session_id, session_date, started_at, ended_at, required_successes, status
- **session_scale_state:** session_id, scale_id, tokens_remaining, successes, failures, last_shown_at
- **attempts:** id, session_id, scale_id, attempt_no, outcome, created_at
- **config:** key, value (required_successes, allow_repeat_when_last_only, show_notes)

## Key Patterns

### HTMX-Driven UI
- Server renders HTML fragments, HTMX swaps them without page reload
- Buttons use `hx-post`, `hx-target`, `hx-swap` attributes
- CSRF tokens injected via `htmx:configRequest` listener in app.js

### Authentication
- Google OAuth2 for admin access
- Admin email: configured via `ADMIN_EMAILS` environment variable (comma-separated)
- User stored in `$_SESSION['user']` = {email, name, picture}
- AuthService handles authorization checks

### Session Isolation
- Each browser gets unique `browser_session_id` (random hex)
- Practice sessions are isolated per browser per day

### Scheduler Algorithm
Weighted random selection:
- Base weight: 1.0 (never attempted) or 2.0 (practiced)
- +0.1 per remaining token (scales needing work get boost)
- +0.5 recency bonus for scales not shown recently
- Prevents same scale twice in row (unless last remaining)

## Environment Variables
```
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=https://your-domain.com/auth/callback
ADMIN_EMAILS=me@awest.uk
APP_KEY=random_hex_key_for_sessions
```

For local development, create a `.env` file in the project root.

## Security Patterns
- CSRF tokens on all POST requests (Slim CSRF middleware)
- Prepared statements (PDO) for SQL injection prevention
- OAuth2 state parameter for auth CSRF protection
- `htmlspecialchars()` for output escaping
- SameSite=Strict cookies

## Common Tasks

### Adding a new route
1. Add route in `app/Router.php`
2. Add controller method in appropriate controller
3. Add CSRF middleware if POST: `->add($csrf)`

### Adding a new config option
1. Create migration in `/migrations/` with next number
2. Insert default value into `config` table
3. Update `SessionService::loadConfig()` if needed
4. Add UI in `views/settings.php`

### Protecting a route with authentication
1. Check `AuthService::isAdmin()` in controller
2. Redirect to login or show unauthorized if not admin
3. Use `AuthService::getUser()` for user data

### Creating a new migration
1. Create file: `migrations/00X_description.php`
2. Extend `App\Storage\Migration`
3. Implement `up()` method with SQL
4. Run: `php bin/migrate.php`

## Testing
- PHPUnit for unit tests
- Test files in `/tests/` directory
- Run: `./vendor/bin/phpunit`

## Version
Current version displayed in `views/layout.php` (update when releasing).
