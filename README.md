# Piano Scale Reps

A touch-friendly web application for practicing piano scales using Anki-style spaced repetition, built with PHP, HTMX, and SQLite.

## Features

- **Token-based progression**: Achieve X first-try successes per scale
- **Smart scheduling**: No immediate repeats, prioritizes least-recently-shown scales
- **Session management**: Auto-create daily sessions, reset options
- **Touch & keyboard support**: Space to advance, Y for success, N for fail
- **Real-time stats**: Live progress tracking with HTMX
- **Responsive design**: Optimized for mobile and desktop

## Tech Stack

- PHP 8.3 with Slim 4 framework
- HTMX for interactive UI without complex JavaScript
- SQLite for data persistence
- Bootstrap 5.3 for styling
- Docker for containerization
- Fly.io for deployment

## Quick Start (Local Development)

### Prerequisites
- PHP 8.2+ with PDO SQLite extension
- Composer

### Setup

1. Install dependencies:
```bash
composer install
```

2. Run database migrations:
```bash
php bin/migrate.php
```

3. Start local server:
```bash
php -S 0.0.0.0:8081 -t public
```

4. Open http://localhost:8081 in your browser

## Deployment to Fly.io

### Prerequisites
- Fly CLI installed (`curl -L https://fly.io/install.sh | sh`)
- Fly.io account

### Deploy Steps

1. Authenticate with Fly:
```bash
flyctl auth login
```

2. Deploy the app (first time):
```bash
# Since fly.toml already exists, just deploy directly
flyctl deploy
```

3. Create persistent volume for SQLite (if not already created):
```bash
flyctl volumes create sqlite_data --size 1 --region lhr
```

4. Set app secret (optional but recommended):
```bash
flyctl secrets set APP_KEY=$(openssl rand -hex 16)
```

5. Open deployed app:
```bash
flyctl open
```

### Alternative: Create New App

If you want to use a different app name or the above doesn't work:

```bash
# Remove existing fly.toml
rm fly.toml

# Launch with your preferred name
flyctl launch --name your-app-name --region lhr

# Then follow steps 3-5 above
```

## Project Structure

```
/public/            # Web root
  index.php         # Entry point
  /assets/          # CSS and JS files
/app/               # Application code
  /Controllers/     # Request handlers
  /Domain/          # Business logic
  /Storage/         # Database layer
  /Models/          # Data models
/views/             # HTML templates
  /fragments/       # HTMX partials
/bin/               # CLI scripts
/data/              # SQLite database (git-ignored)
```

## Configuration

Settings can be adjusted in the web UI:
- **Required Successes (X)**: 1-10 tokens per scale
- **Allow Repeat**: When only one scale remains
- **Manage Scales**: Add/remove practice scales

## How It Works

1. Each scale starts with X tokens (default: 3)
2. Success decrements token by 1
3. Failure resets tokens to X
4. Scale is complete when tokens reach 0
5. Session ends when all scales are complete

## Keyboard Shortcuts

- **Space**: Show next scale / advance
- **Y**: Mark as success
- **N**: Mark as failure

## Database Schema

- `scales`: Library of practice scales
- `sessions`: Daily practice sessions
- `session_scale_state`: Per-scale progress tracking
- `attempts`: Individual attempt history
- `config`: Global settings

## Development

### Running Tests
```bash
composer test
```

### Building Docker Image
```bash
docker build -t piano-scale-reps .
docker run -p 8081:8081 piano-scale-reps
```

### Monitoring Logs
```bash
flyctl logs
```

### SSH into Production
```bash
flyctl ssh console
```

## Security

- CSRF protection on all POST requests
- SameSite cookies
- Prepared statements for SQL queries
- No user authentication (single-user app)

## License

MIT