# YMB-MET — Technical Architecture (Laravel MVP)

## Stack
- Laravel 10 (PHP 8.1) + Livewire 3 + Blade + Alpine
- MySQL (XAMPP)
- Spatie Permission
- Laravel Mail (sync/log locally)
- Sanctum ready (API later)
- No `php artisan serve`: Apache at `http://localhost/ymb-met/` via root `.htaccess` → `public/`

## Soft pot control (MVP)
- Warn when approval would push committed + spent over monthly budget
- Do not hard-block; Phase 2 may add HoM force-approve

## Core models
- User (roles: initiator, spender, approver, head_of_marketing, admin, super_admin)
- Team, Category
- MonthlyBudget (year, month, amount_bdt)
- BudgetRequest (spender_id, initiator_id, team, category, amounts, dates, budget_month, status, backdate fields)
- ApprovalStep / ApprovalAction
- ActualExpense
- Attachment
- AuditLog
- Notification (database + mail)

## Money rules
- Available = monthly_budget − committed − actual (for Budget month)
- Commit on final line approval; release unused on close/cancel
- Pending does not reserve
- Actuals attribute to request Budget month, not spend date
- Overrun: allow + require justification; no re-approval

## Backdate
- request_date < today → Awaiting Super Admin → then normal spender chain

## Deploy
- Local: XAMPP Apache subdirectory
- cPanel: public as docroot or same rewrite; web installer for migrate without SSH
