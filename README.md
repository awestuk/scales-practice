# Piano Scale Reps 🎹

A touch-friendly web application for mastering piano scales through intelligent spaced repetition. Build muscle memory and consistency by practicing scales with a proven streak-based system.

## How It Works

### The Streak System
Each scale requires a configurable number of **first-try successes** (default: 3) to complete:
- ✅ **Success**: Moves you closer to completing the scale (reduces goals by 1)
- ❌ **Failure**: Resets the scale back to the starting number of goals
- 🎯 **Goal**: Achieve the required successes *in a row* for each scale to build consistency

### Practice Flow
1. **Start**: Click "Show Next Scale" or press `Space`
2. **Play**: Practice the scale shown on your piano
3. **Record**: Mark your attempt as "Nailed It!" (`Y`) or "Missed" (`N`)
4. **Progress**: The app intelligently selects your next scale
5. **Complete**: Finish when all scales reach their goal!

### Smart Scheduling Algorithm
The app uses a **weighted random selection** system that creates natural, engaging practice sessions:

**How it works:**
- **Scales you're practicing** (already attempted) are **2x more likely** to appear than new scales
- **Scales needing more work** get a slight bonus (based on remaining streak goals)
- **Older scales** get a small boost to prevent any scale from being ignored too long
- **Never shows the same scale twice in a row** (unless it's the last one)

**The result:** Instead of forcing you through all 20+ scales sequentially, you'll see the scales you're working on more frequently while still getting exposed to new ones. This creates a more natural practice flow that's neither too repetitive nor too scattered.

**Example weights:**
- Never attempted scale: base weight 1.0
- Recently practiced scale: base weight 2.0
- Scale needing 3 more successes: +0.3 bonus
- Scale not seen in a while: up to +0.5 recency bonus

## Features

- 🎯 **Streak-based progression**: Build consistency with consecutive successes
- 🧠 **Intelligent scheduling**: Optimized spaced repetition for better retention
- 📱 **Touch-friendly**: Works perfectly on tablets and phones
- ⌨️ **Keyboard shortcuts**: `Space` to advance, `Y` for success, `N` for failure
- 📊 **Live statistics**: Track attempts, successes, and progress in real-time
- 🏆 **Celebration animations**: Rewarding completion screen with confetti
- 👥 **Multi-user support**: Each browser session gets its own isolated data
- ⚙️ **Customizable settings**: Adjust success requirements and scale library

## Available Scales

The app comes preloaded with a comprehensive practice set:

### Major Scales
- All 12 major scales (C, G, D, A, E, B, F, B♭, E♭, A♭, D♭, G♭)

### Minor Scales
- C# Minor (Harmonic & Melodic)
- E Minor (Harmonic & Melodic)
- G Minor (Harmonic & Melodic)
- B♭ Minor (Harmonic & Melodic)

### Third Apart Exercises
Practice scales in thirds for advanced finger independence:
- Major scales: D♭, E, G, B♭
- Minor harmonic scales: C#, E, G, B♭

You can add or remove scales through the Settings page to customize your practice routine.

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