<div align="center">

<img src="assets/images/logo.svg" alt="SubTrack Logo" width="72" height="72">

# SubTrack

**A clean, modern subscription tracking web application.**

Track every subscription, never miss a renewal, and know exactly how much you spend each month.

[![PHP](https://img.shields.io/badge/PHP-8.x-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.x-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind-CSS-38BDF8?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-22C55E?style=flat-square)](LICENSE)

[Features](#features) · [Demo](#demo) · [Installation](#installation) · [Configuration](#configuration) · [Architecture](#architecture) · [Contributing](#contributing)

</div>

---

## Overview

SubTrack is a self-hosted, multi-user subscription tracker built with vanilla PHP 8, MySQL, and Tailwind CSS. It has no framework dependencies — just clean, readable code you can deploy on any shared hosting with PHP and MySQL, including **Fasthosts**.

Users register for their own account and manage their personal subscriptions (Netflix, Spotify, iCloud, Adobe, mobile contracts, and more). The app always knows the next billing date, shows upcoming payments in a calendar view, and surfaces spending analytics through clean Chart.js charts.

---

## Features

### 📊 Dashboard
- **Summary widgets** — Monthly total, annual total, active subscription count, next upcoming payment
- **Upcoming payments** — Next 30 days at a glance
- **Quick actions** — One-click navigation to every section

### 📅 Calendar View
- Full monthly grid showing every billing date
- Colour-coded chips per subscription (by category colour)
- Click any date or chip to see a full breakdown modal
- AJAX month navigation — no page reloads
- Mobile-friendly: switches to list view on small screens

### 📈 Analytics
- **Monthly spend chart** — Bar chart of actual payments over last 12 months
- **Category breakdown** — Doughnut chart with category colours from the database
- **Billing cycle breakdown** — All cycles normalised to monthly equivalent for fair comparison
- All charts powered by [Chart.js](https://chartjs.org) (loaded from CDN)

### 🔁 Subscription Management
- Add, edit, pause, resume, and delete subscriptions
- 5 billing cycles: **weekly · monthly · quarterly · biannual · annual**
- Correct next-billing-date calculation for all cycles (including month-end edge cases, e.g. Jan 31 → Feb 28)
- Multi-currency support (GBP default, 18 currencies supported)
- Logo auto-fetch via favicon when a URL is provided
- Category icon grid selector (12 categories with Heroicons)
- Status badges: Active / Paused / Cancelled

### 🔔 Reminders
- Per-subscription email reminders: 1, 2, 3, 5, or 7 days before billing date
- Global reminder settings page
- Cron-powered delivery via `scripts/send_reminders.php`

### 💰 Payment History
- Full log of all payments per subscription
- Filter by date range, subscription, or category
- CSV export (with UTF-8 BOM for Excel compatibility)

### 🔐 Auth & Security
- Email/password registration with bcrypt (cost 12)
- **Google OAuth2** sign-in via `google/apiclient`
- Automatic account linking when same email exists
- CSRF protection on every POST form
- Rate limiting: 5 failed logins → 15-minute lockout
- `session_regenerate_id(true)` on every login
- All queries use PDO prepared statements — no string interpolation
- Every query filters by `user_id` from session — no cross-user data leakage
- Role-based access: `user` / `admin`

### 🌙 Light / Dark Mode
- Auto-detects system preference
- Persisted in `localStorage` + AJAX → database
- All Chart.js charts adapt to dark mode

### 📤 Import & Export
- CSV import of subscriptions (for users migrating from spreadsheets)
- Full data export: subscriptions + payment history

### 🛡️ GDPR Compliance
- Cookie consent banner (Necessary / Analytics tiers)
- First-time Google login shows GDPR consent screen
- Right to erasure: deletes account + all subscriptions + all payment records
- GDPR-compliant Privacy Policy
- Consent checkboxes never pre-ticked

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 8.x — vanilla, no frameworks, no namespaces |
| Database | MySQL 8.x — PDO only, prepared statements throughout |
| Frontend | Tailwind CSS (standalone binary), Vanilla JS |
| Charts | Chart.js (CDN) |
| Email | PHPMailer |
| OAuth | `google/apiclient:^2.0` |
| Testing | PHPUnit 10 |

> **No Laravel. No Symfony. No MVC. No ORM. No npm.**

---

## Demo

> _Add a demo URL here once deployed._

---

## Requirements

- PHP 8.0 or higher (8.2+ recommended)
- MySQL 8.0 or higher
- Composer
- Web server: Apache or Nginx (or PHP built-in server for development)

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/neojakey/subtrack.git
cd subtrack
```

### 2. Install PHP dependencies

`vendor/` is committed to the repository for Fasthosts shared hosting compatibility, so you may not need to run this. If vendor is absent:

```bash
composer install --no-dev --optimize-autoloader
```

### 3. Create your database

```sql
CREATE DATABASE subtrack CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'subtrack_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON subtrack.* TO 'subtrack_user'@'localhost';
FLUSH PRIVILEGES;
```

### 4. Apply the schema

```bash
mysql -u subtrack_user -p subtrack < sql/schema.sql
```

This creates all tables and seeds the 12 subscription categories.

### 5. Configure environment

```bash
cp .env.example .env
```

Edit `.env` with your values (see [Configuration](#configuration) below).

### 6. Build Tailwind CSS

Download the [Tailwind CSS standalone CLI](https://github.com/tailwindlabs/tailwindcss/releases) binary for your platform and place it in the project root, then:

```bash
./tailwindcss -i assets/css/input.css -o assets/css/output.css --minify
```

For development with auto-rebuild:

```bash
./tailwindcss -i assets/css/input.css -o assets/css/output.css --watch
```

> `assets/css/output.css` is committed to the repository, so this step is only needed when making CSS changes.

### 7. Start the development server

```bash
php -S localhost:8000 -t /path/to/subtrack
```

Visit `http://localhost:8000` in your browser.

---

## Configuration

All configuration is done in `.env`. Copy `.env.example` to get started:

```ini
# Application
APP_ENV=local                    # 'local' or 'production'
APP_URL=http://localhost:8000    # Base URL — no trailing slash

# Database
DB_HOST=localhost
DB_NAME=subtrack
DB_USER=your_db_user
DB_PASS=your_db_password

# Email (SMTP) — used for verification, reminders, password reset
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USER=you@example.com
MAIL_PASS=your_smtp_password
MAIL_FROM=noreply@subtrack.app
MAIL_FROM_NAME=SubTrack
MAIL_ENCRYPTION=tls              # 'tls' or 'ssl'

# Google OAuth2 (optional — leave blank to disable Google Sign-In)
GOOGLE_CLIENT_ID=your-client-id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your-client-secret
GOOGLE_REDIRECT_URI=https://yourdomain.com/auth/google-callback.php

# Exchange rate API (optional — leave blank for hardcoded fallback rates)
EXCHANGE_RATE_API_KEY=           # Free key from exchangerate-api.com
```

### Setting up Google OAuth

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select an existing one)
3. Enable the **Google+ API** / **Google People API**
4. Go to **Credentials → Create Credentials → OAuth client ID**
5. Application type: **Web application**
6. Authorised redirect URIs: `https://yourdomain.com/auth/google-callback.php`
7. Copy the **Client ID** and **Client Secret** into `.env`

---

## Cron Jobs

Set up the following cron jobs on your server:

```cron
# Send billing reminders (daily at 08:00)
0 8 * * * php /path/to/subtrack/scripts/send_reminders.php >> /path/to/subtrack/logs/cron.log 2>&1

# Advance billing dates after payment due (daily at 00:05)
5 0 * * * php /path/to/subtrack/scripts/advance_billing_dates.php >> /path/to/subtrack/logs/cron.log 2>&1

# Refresh exchange rates (daily at 06:00)
0 6 * * * php /path/to/subtrack/scripts/refresh_exchange_rates.php >> /path/to/subtrack/logs/cron.log 2>&1

# Weekly digest email to opted-in users (Monday at 08:00)
0 8 * * 1 php /path/to/subtrack/scripts/send_weekly_digest.php >> /path/to/subtrack/logs/cron.log 2>&1
```

---

## Architecture

SubTrack uses a clean, flat file architecture — no MVC, no namespaces, no framework magic. Every file is straightforward to follow.

```
/
├── admin/                     # Admin-only pages (users.php, logs.php)
├── ajax/                      # AJAX handlers (set_theme, delete, mark_paid, calendar)
├── assets/
│   ├── css/
│   │   ├── input.css          # Tailwind source + component layer
│   │   └── output.css         # Compiled CSS (committed)
│   ├── js/
│   └── images/
├── auth/                      # login, register, logout, Google OAuth, password reset
├── classes/
│   ├── Config.php             # .env loader
│   ├── Database.php           # Singleton PDO connection
│   ├── Session.php            # Session wrapper
│   ├── Input.php              # Request sanitisation
│   ├── Csrf.php               # CSRF token management
│   ├── Logger.php             # File-based debug logger
│   ├── UrlHelper.php          # URL generation
│   ├── SecurityHelper.php     # Auth guards, bcrypt, rate limiting, IDOR protection
│   ├── DateHelper.php         # Billing cycle calculations
│   ├── CurrencyHelper.php     # Currency formatting & conversion
│   ├── UIHelper.php           # All reusable PHP UI components
│   ├── UserRepository.php     # User CRUD + Google linking
│   ├── SubscriptionRepository.php
│   ├── CategoryRepository.php
│   ├── PaymentLogRepository.php
│   ├── CurrencyRepository.php
│   ├── ReminderRepository.php
│   ├── CalendarService.php    # Builds monthly calendar event map
│   ├── AnalyticsService.php   # Spend aggregation for charts
│   ├── GoogleAuthService.php  # OAuth2 via google/apiclient
│   ├── ReminderService.php    # Sends due email reminders
│   ├── CurrencyService.php    # Fetches live exchange rates
│   ├── ExportService.php      # CSV export/import
│   ├── Mailer.php             # PHPMailer wrapper + email templates
│   └── StripeService.php      # Stub for future Pro tier
├── components/
│   └── cookie_banner.php
├── config/
│   └── config.php             # Bootstrap: load .env, start session, autoload classes
├── dashboard/                 # All authenticated dashboard pages
├── errors/                    # 403.php, 404.php, 500.php
├── includes/
│   ├── header.php
│   └── footer.php
├── logs/                      # app_debug.log (git-ignored)
├── scripts/                   # Cron scripts
├── sql/
│   └── schema.sql             # Full schema + seed data
├── tests/                     # PHPUnit test suite
├── .env.example
├── .gitignore
├── composer.json
└── README.md
```

### Billing Cycle Logic

The `DateHelper::nextBillingDate()` method correctly handles all five billing cycles including the February edge case:

| Cycle | Logic |
|---|---|
| Weekly | +7 days |
| Monthly | Same day next month — clamped to last day if shorter (e.g. Jan 31 → Feb 28) |
| Quarterly | Same day, +3 months |
| Biannual | Same day, +6 months |
| Annual | Same day, +1 year |

**Monthly equivalents** (for analytics):

| Cycle | Formula |
|---|---|
| Weekly | `amount × 52 / 12` |
| Monthly | `amount` |
| Quarterly | `amount / 3` |
| Biannual | `amount / 6` |
| Annual | `amount / 12` |

---

## Running Tests

```bash
# Run the full test suite
./vendor/bin/phpunit --testdox

# Run a specific test file
./vendor/bin/phpunit tests/DateHelperTest.php
```

Tests use a separate `subtrack_test` database. All external services (Google, SMTP, exchange rate API) are mocked.

---

## Security

- **CSRF**: Every POST form includes a session token validated with `hash_equals()`
- **SQL injection**: 100% PDO prepared statements — no string interpolation in queries
- **XSS**: All output through `htmlspecialchars()`, all input through `Input::sanitize()`
- **Rate limiting**: 5 failed logins triggers a 15-minute lockout
- **Password hashing**: bcrypt with cost 12 via `password_hash()`
- **Google OAuth state**: Validated on every callback — mismatch returns HTTP 403
- **User isolation**: Every query filters by `user_id` from session
- **Session security**: `httponly` + `samesite=Lax` cookies; `session_regenerate_id(true)` on login
- **IDOR protection**: `SecurityHelper::ownsResource()` checks on all resource access

---

## Deployment on Fasthosts

1. Upload files via FTP or Git
2. `vendor/` is committed, so no `composer install` needed on the server
3. `assets/css/output.css` is committed, so no Tailwind build step needed
4. Set up the database and run `sql/schema.sql`
5. Create `.env` from `.env.example` and fill in production values
6. Set up cron jobs as described above

---

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch: `git checkout -b feat/your-feature`
3. Commit using [Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `docs:`, etc.
4. Push and open a Pull Request

Please make sure all existing PHPUnit tests pass before submitting.

---

## Roadmap

- [ ] Pro tier with Stripe billing (stub already in `classes/StripeService.php`)
- [ ] Browser extension for auto-detecting subscription pages
- [ ] Mobile PWA (service worker + manifest)
- [ ] Currency auto-conversion in summary widgets
- [ ] Shared subscription splitting between users

---

## Licence

MIT — see [LICENSE](LICENSE) for details.

---

<div align="center">
Built with ☕ using PHP 8, MySQL, and Tailwind CSS.
</div>
