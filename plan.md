# Piano Scale Reps - Implementation Plan

## Architecture Overview
- **Backend**: PHP 8.3 with Slim 4 framework for routing
- **Frontend**: HTMX for interactivity, Bootstrap 5.3 for styling
- **Database**: SQLite with PDO
- **Deployment**: Docker (Apache + PHP) on Fly.io port 8081

## Key Design Decisions

### HTMX Integration
- Server returns HTML fragments instead of JSON
- All interactions via HTMX attributes (hx-post, hx-get, hx-target)
- Minimal JavaScript only for keyboard shortcuts
- Progressive enhancement approach

### Token-Based Progression
- Each scale starts with X tokens (configurable 1-10)
- Success: decrements token by 1
- Failure: resets tokens back to X
- Scale completed when tokens reach 0

### Smart Scheduling Algorithm
1. Filter scales with tokens_remaining > 0
2. Sort by:
   - Never shown first (NULL last_shown_at)
   - Oldest last_shown_at
   - Higher tokens_remaining
   - Random tiebreak
3. Avoid immediate repeats unless only 1 scale active

### Session Management
- Auto-create today's session on first visit
- Snapshot required_successes (X) at session start
- "Reset Session": close current, start fresh today
- "Start New Day": available when last session < today

## Database Schema

```sql
-- Scales library
CREATE TABLE scales (
    id INTEGER PRIMARY KEY,
    name TEXT UNIQUE NOT NULL,
    notes TEXT
);

-- Global configuration
CREATE TABLE config (
    key TEXT PRIMARY KEY,
    value TEXT
);

-- Session tracking
CREATE TABLE sessions (
    id INTEGER PRIMARY KEY,
    session_date TEXT NOT NULL,
    started_at TEXT NOT NULL,
    ended_at TEXT,
    required_successes INTEGER NOT NULL,
    status TEXT CHECK(status IN ('active', 'completed'))
);

-- Per-scale session state
CREATE TABLE session_scale_state (
    session_id INTEGER,
    scale_id INTEGER,
    tokens_remaining INTEGER NOT NULL,
    successes INTEGER DEFAULT 0,
    failures INTEGER DEFAULT 0,
    last_shown_at INTEGER,
    PRIMARY KEY (session_id, scale_id)
);

-- Attempt history
CREATE TABLE attempts (
    id INTEGER PRIMARY KEY,
    session_id INTEGER NOT NULL,
    scale_id INTEGER NOT NULL,
    attempt_no INTEGER NOT NULL,
    outcome TEXT CHECK(outcome IN ('success', 'fail')),
    created_at TEXT NOT NULL
);
```

## UI Flow

1. **Initial Load**: Server renders home page with current state
2. **Get Scale**: Button with `hx-post="/next-scale"` loads scale card
3. **Reveal Outcome**: Shows success/fail buttons
4. **Record Outcome**: `hx-post="/attempt"` updates and gets next scale
5. **Stats Update**: `hx-trigger="every 2s"` for live badges

## HTMX Endpoints

- `GET /` - Full page render
- `POST /next-scale` - Returns scale card HTML fragment
- `POST /attempt` - Records outcome, returns next state
- `GET /stats-badges` - Returns updated stats HTML
- `POST /reset-session` - Resets and returns fresh UI
- `GET /settings` - Settings page
- `POST /settings` - Updates config

## Security Features
- CSRF tokens via Slim-CSRF middleware
- SameSite=Strict cookies
- Prepared statements for all queries
- Input validation and sanitization

## File Structure
```
/public/
    index.php           # Entry point
    .htaccess          # Apache rewrite rules
    /assets/
        app.css        # Custom styles
        app.js         # Keyboard shortcuts only
/app/
    Router.php         # Slim 4 route definitions
    /Controllers/
        UiController.php    # Page renders
        ApiController.php   # HTMX endpoints
    /Domain/
        Scheduler.php       # Scale selection logic
        SessionService.php  # Session management
        StatsService.php    # Statistics calculations
    /Storage/
        Db.php             # PDO connection
        Migrations.php     # Schema setup
    /Models/
        Scale.php          # Scale entity
        Session.php        # Session entity
        Attempt.php        # Attempt entity
/views/
    layout.php         # Base HTML template
    home.php          # Main practice view
    settings.php      # Configuration view
    /fragments/       # HTMX partial templates
/bin/
    migrate.php       # Database setup script
/data/
    .gitignore        # Ignore SQLite files
composer.json         # PHP dependencies
Dockerfile           # Multi-stage build
fly.toml            # Fly.io configuration
.dockerignore       # Build exclusions
README.md           # Quick start guide
```

## Performance Optimizations
- SQLite WAL mode for concurrent reads
- Indexed columns for scheduling queries
- Lazy loading with HTMX
- HTTP/2 push for assets
- Scale-to-zero on Fly.io

## Testing Strategy
- Unit tests for Scheduler logic
- Integration tests for session flow
- Manual testing for touch interactions
- Health check endpoint validation