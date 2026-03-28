# 📅 Smart Scheduling System

> **Adaptive appointment scheduler with SMS notifications.** Unlike rigid fixed-slot systems that waste productive time, this application detects gaps in the schedule automatically — if a 1-hour procedure is booked in a 2-hour block, the system identifies the remaining time and generates a new available slot on the fly.

[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![Twilio](https://img.shields.io/badge/SMS-Twilio_API-F22F46?style=flat&logo=twilio&logoColor=white)](https://twilio.com)
[![Sanctum](https://img.shields.io/badge/Auth-Sanctum-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com/docs/sanctum)
[![Pest](https://img.shields.io/badge/Tests-Pest-orange?style=flat)](https://pestphp.com)
[![SQLite](https://img.shields.io/badge/Database-SQLite-003B57?style=flat&logo=sqlite&logoColor=white)](https://sqlite.org)

---

## The problem this solves

Traditional scheduling systems work with fixed time slots: 9h, 10h, 11h. If a client books a 30-minute appointment in a 1-hour slot, the remaining 30 minutes are silently lost — the system doesn't offer that time to anyone else.

This application implements **adaptive scheduling logic**: when a booking is made, the system calculates whether the remaining time in the block is enough for another appointment. If it is, a new available slot is created automatically. No wasted time, no manual intervention.

---

## How it works

```
Client books a procedure (e.g. 1h) in a 2h block
            │
            ▼
┌──────────────────────────────────┐
│      Gap Detection Engine        │
│                                  │
│  block_duration - procedure = Δt │
│  if Δt ≥ min_slot → create slot  │
└──────────────┬───────────────────┘
               │
       ┌───────┴────────┐
       ▼                ▼
  Save booking     Generate new
  to database      available slot
       │
       ▼
┌──────────────────┐
│   Twilio SDK     │  Send SMS confirmation
│   Notification   │  to client's phone
└──────────────────┘
```

---

## Features

- **Adaptive slot generation** — leftover time in a block becomes a new bookable slot automatically
- **SMS confirmations** — appointment confirmations sent via Twilio API at booking time
- **Token-based auth** — protected routes via Laravel Sanctum
- **Queue-backed jobs** — notifications dispatched asynchronously, keeping API responses fast
- **Test suite** — coverage with Pest PHP

---

## Tech stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 (PHP 8.2) |
| Authentication | Laravel Sanctum |
| SMS Notifications | Twilio SDK `^8.10` |
| Database | SQLite |
| Testing | Pest PHP `^4.2` |
| Frontend assets | Vite + Tailwind CSS |
| Queue | Database-backed queue driver |

---

## Getting started

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- A [Twilio account](https://www.twilio.com/) (free trial works)

### One-command setup

The project includes a `composer setup` script that handles everything:

```bash
git clone https://github.com/samuel-Mdcosta/Sistema-de-agendamento.git
cd Sistema-de-agendamento
composer setup
```

This command installs PHP dependencies, copies `.env.example` to `.env`, generates the app key, runs migrations, installs JS packages, and builds frontend assets.

### Manual setup

```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Create SQLite database and run migrations
touch database/database.sqlite
php artisan migrate

# Build frontend assets
npm run build
```

### Environment variables

Edit `.env` and add your Twilio credentials:

```env
APP_NAME="Smart Scheduling"
APP_URL=http://localhost

DB_CONNECTION=sqlite

# Twilio — get these from twilio.com/console
TWILIO_SID=your_account_sid
TWILIO_TOKEN=your_auth_token
TWILIO_FROM=+1your_twilio_number
```

### Running the full development stack

```bash
composer dev
```

This starts three processes concurrently: the Laravel server, the queue worker (for async SMS dispatch), and Vite for frontend assets.

---

## Running tests

```bash
composer test
# or directly:
php artisan test
```

The test suite uses Pest PHP with the Laravel plugin. Tests clear the config cache before running to ensure a clean environment.

---

## Project structure

```
Sistema-de-agendamento/
├── app/
│   ├── Http/Controllers/    # Route handlers
│   ├── Models/              # Eloquent models
│   └── ...
├── database/
│   └── migrations/          # Schema definitions
├── resources/
│   ├── views/               # Blade templates
│   └── css/ js/             # Frontend assets (Vite)
├── routes/
│   ├── web.php
│   └── api.php
├── tests/                   # Pest test suite
├── composer.json            # includes `composer setup` and `composer dev` scripts
└── .env.example
```

---

## Design decisions

**Why adaptive slots instead of fixed ones?**
Fixed-slot systems are simple to implement but wasteful in practice. A clinic, barbershop, or any service business with variable-duration appointments loses productive time every day. The gap-detection logic runs on every booking and surfaces that time immediately — no manual calendar management needed.

**Why Twilio for SMS?**
Email confirmations have low open rates for appointment reminders. SMS via Twilio reaches clients reliably and integrates cleanly as a queued job, keeping the booking response fast even when the SMS delivery takes a moment.

**Why queue-backed notifications?**
Dispatching SMS synchronously would add Twilio's API latency to every booking response. By dispatching notifications as queued jobs, the booking endpoint responds immediately while the SMS is sent in the background by the queue worker.

---

## Author

**Samuel M. Costa** — Backend Developer | Laravel · PHP · Python · AI & LLMs

- LinkedIn: [linkedin.com/in/samuelmdcosta](https://linkedin.com/in/samuelmdcosta)
- Email: costadev19@gmail.com
- GitHub: [github.com/samuel-Mdcosta](https://github.com/samuel-Mdcosta)
