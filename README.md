# Yamaha Marketing Expense Tracker (YMB-MET)

Laravel 10 + Livewire 3 + MySQL marketing expense system for Yamaha Motorcycles Bangladesh (ACI Motors).

## Requirements

- PHP 8.1+ (XAMPP)
- Composer
- Node.js (for Vite assets — already built in `public/build`)
- MySQL/MariaDB (XAMPP)
- Apache with `mod_rewrite`

## Local setup (XAMPP)

### 1. Start services

1. Open **XAMPP Control Panel**
2. Start **Apache** and **MySQL**

### 2. Create database

Using phpMyAdmin or MySQL CLI:

```sql
CREATE DATABASE ymb_met CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

CLI example:

```bash
d:\xampp\mysql\bin\mysql.exe -u root -e "CREATE DATABASE IF NOT EXISTS ymb_met CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### 3. Configure environment

Copy `.env.example` to `.env` if needed. Key settings:

```env
APP_URL=http://localhost/ymb-met
DB_DATABASE=ymb_met
DB_USERNAME=root
DB_PASSWORD=
MAIL_MAILER=log
QUEUE_CONNECTION=sync
INSTALL_TOKEN=ymb-met-install
```

Generate app key if missing:

```bash
php artisan key:generate
```

### 4. Install dependencies (if not already done)

```bash
composer install
npm install
npm run build
```

### 5. Migrate and seed

**Option A — Terminal:**

```bash
php artisan migrate --seed
php artisan storage:link
```

**Option B — Web installer (no terminal / cPanel-friendly):**

1. Open `http://localhost/ymb-met/install`
2. Enter token: `ymb-met-install` (or your `INSTALL_TOKEN`)
3. Click **Install now**

### 6. Open the app

**URL:** [http://localhost/ymb-met/](http://localhost/ymb-met/)

> Do **not** use `php artisan serve`. Apache serves the app via root `.htaccess` → `public/`.

## Demo logins

All demo passwords: **`password`**

After role changes, re-sync demo users:

```bash
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=DemoSeeder
```

| Account | Email | Roles | Capabilities |
|---------|-------|-------|--------------|
| Super Admin | superadmin@ymb.test | super_admin, admin, staff | Initiate/spend/report actuals + backdate clearance + admin |
| Head of Marketing | hom@ymb.test | staff, head_of_marketing | Initiate/spend/report actuals + HoM dashboard + admin |
| Admin | admin@ymb.test | admin, staff | Initiate/spend/report actuals + admin config |
| Line Manager | approver@ymb.test | staff, approver (legacy) | Initiate/spend/report actuals + approves via manager chain |
| Field Spender | spender@ymb.test | staff | Initiate/spend/report actuals |
| Marketing Initiator | initiator@ymb.test | staff | Initiate/spend/report actuals |

**Manager chain (spender → line manager → HoM):** Field Spender reports to Line Manager, who reports to Head of Marketing. Approval follows the spender’s chain, not a separate approver-only role.

**Demo data:** Marketing Operations team, 10 categories, current month budget **BDT 5,000,000**.

## Features (Phase 1 MVP)

- Unified **staff** role: marketing users can initiate, be selected as spender, and report actuals
- Chain-based approval along spender's `manager_id` line (not a separate exclusive approver role)
- Backdated requests (`request_date` before today) → Super Admin clearance → spender approval chain
- Commit on final approval; cancel/close releases unused commitment
- Actual expenses by initiator or spender; overrun justification required
- HoM dashboard: budget / committed / spent / available, overruns, variance, backdated filter
- Soft pot warning on approval (warn only, no hard block)
- In-app + email (log driver) notifications
- Immutable audit log
- CSV export of requests
- Admin: teams, categories, monthly budgets
- Yamaha-themed responsive UI

## Useful commands

```bash
php artisan route:list
php artisan migrate:fresh --seed
```

## Mail

Locally, emails are written to `storage/logs/laravel.log` (`MAIL_MAILER=log`).

## Project structure notes

- Plan: `.cursor/plans/marketing_expense_tracker.plan.md` (do not edit from builds)
- Architecture: `internal/ARCHITECTURE.md`
- Logo: `public/images/yamaha-logo.jpg`

## Troubleshooting

| Issue | Fix |
|-------|-----|
| 404 on `/ymb-met/` | Enable Apache `mod_rewrite`; ensure root `.htaccess` exists |
| CSS/JS broken | Run `npm run build`; verify `APP_URL=http://localhost/ymb-met` |
| DB connection refused | Start MySQL in XAMPP |
| `Class not found` | Run `composer dump-autoload` |
