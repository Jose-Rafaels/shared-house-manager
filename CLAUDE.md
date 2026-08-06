# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Docker

All `php artisan`, `./vendor/bin/pint`, and `npm` commands must run inside the Docker container. **Always use `docker exec -it shared-house-manager-app`** — never run these directly on the host.

The container mounts the repo at `/var/www/html` and serves on host port `8000`. It joins an external `mysql_default` network (MySQL lives in a separate compose project).

```bash
# Start the Docker environment (first time: create network, then up)
docker network create mysql_default
docker compose up -d
```

## Commands

All commands below use `docker exec -it shared-house-manager-app`:

```bash
# Run all tests
docker exec -it shared-house-manager-app php artisan test

# Run a specific test file
docker exec -it shared-house-manager-app php artisan test tests/Unit/DebtLedgerServiceTest.php

# Run a specific test method
docker exec -it shared-house-manager-app php artisan test --filter test_outstanding_balances

# Run unit tests only
docker exec -it shared-house-manager-app php artisan test --testsuite=Unit

# Run feature tests only
docker exec -it shared-house-manager-app php artisan test --testsuite=Feature

# Lint with Laravel Pint
docker exec -it shared-house-manager-app ./vendor/bin/pint

# Start dev server (or just use the container's port 8000)
docker exec -it shared-house-manager-app php artisan serve

# Build frontend assets
docker exec -it shared-house-manager-app npm run build

# Dev frontend (Vite hot-reload)
docker exec -it shared-house-manager-app npm run dev

# Artisan shell (for running multiple commands interactively)
docker exec -it shared-house-manager-app bash
```

## Architecture

### Overview

A Laravel 10 monolith for managing shared household expenses, debts, chores, and shopping. No user accounts — housemates are simple `Member` records. The frontend uses Blade templates with Tailwind CSS v4 (via Vite).

### Module Structure

Five feature modules, each with a consistent pattern:

| Module | Controller | Service(s) | Routes |
|--------|-----------|------------|--------|
| Members | `MemberController` | — | CRUD + soft-delete (archive) |
| Expenses | `ExpenseController` | `MoneySplitService`, `DebtLedgerService` | Create, list, update, delete, toggle split settled |
| Settlements | `SettlementController` | `DebtLedgerService` | CRUD (manual debt repayments) |
| Chores | `ChoreController` | — | CRUD + mark assignment complete |
| Shopping | `ShoppingController` | — | CRUD + toggle purchased |
| Dashboard | `DashboardController` (invokable) | `DashboardService` | `/` (aggregated summary) |

### Data Flow

```
Route (web.php) → Controller → Service (business logic) → Model (Eloquent)
                                                      → ActivityLogService (audit trail)
Controller returns view() with data, or back()->with('status'/'error') redirects
```

- **Controllers** are thin — they validate via FormRequest, delegate to services, log activity, and redirect `back()`. Mutating actions are wrapped in `DB::transaction()`.
- **Services** contain business logic (splitting money, computing balances, netting mutual debts). They are plain PHP classes resolved via Laravel's auto-injection.
- **FormRequests** (`app/Http/Requests/`) define validation rules. All `authorize()` to `true` (no auth). `SettlementRequest::withValidator()` additionally rejects settlements that exceed the outstanding balance (uses `DebtLedgerService::balanceBetween()`).
- **ActivityLogService** is injected into every controller action that mutates data. It logs to the `activity_logs` table with polymorphic `subject_type`/`subject_id` and a JSON `metadata` column.

### Key Models

- **Member** — soft-deleted via `SoftDeletes` (`deleted_at`). `scopeActive()` filters to non-archived. Has `joined_at`. Referenced across modules as `payer_id`, `member_id`, `from_member_id`/`to_member_id`, `assigned_to_member_id`, `added_by_member_id`.
- **Expense** — one `payer` (Member) pays for the group; one `Category`; has many `ExpenseSplit`. `is_fully_settled` accessor returns true when every split has `is_settled = true` (and splits are non-empty). Editing/deleting an expense is blocked when fully settled.
- **ExpenseSplit** — one row per participating member with `amount_owed`. `is_settled` is toggled manually via `ExpenseController::toggleSplit`. `settled`/`unsettled` scopes.
- **Settlement** — a manual debt repayment from `from_member_id` (debtor) to `to_member_id` (creditor). Validated against outstanding balance.
- **Category** — simple name lookup, `hasMany` expenses.
- **Chore** — assigned to a **single** member (`assigned_to_member_id`) for an optional date (`assigned_for_date`). Rotation was removed; history is tracked via `ChoreAssignment`.
- **ChoreAssignment** — historical record of a member assigned to a chore for a date, completed via `completed_at`. Created/completed through `ChoreController::complete`.
- **ShoppingItem** — has `priority` (High/Medium/Low), `added_by_member_id`, `is_purchased` flag (no separate purchase table).
- **ActivityLog** — polymorphic audit trail with `subject_type`/`subject_id` and JSON `metadata`.

### Money & Debt Logic

Two services own all money math. Money is stored as integers (rupiah, no decimals).

- **`MoneySplitService::splitEvenly(int $totalAmount, int $participants): array`** — divides an integer amount across N participants, distributing the remainder (`$totalAmount % $participants`) as `+1` to the first R participants. Returns integer shares that always sum exactly to the input.
- **`DebtLedgerService`** — the core of the debt system:
  - `outstandingBalances()` — computes debtor→creditor balances by summing `ExpenseSplit.amount_owed` (excluding the payer's own share) minus `Settlement.amount`, grouped per ordered pair. Negative amounts are surfaced (overpayment) rather than dropped.
  - `balanceBetween($debtorId, $creditorId, $ignoreSettlementId = null)` — outstanding balance one-way; `ignoreSettlementId` excludes the in-flight settlement during updates.
  - `netShare(Expense $expense, $memberId, $share)` — called when creating an expense. If the payer already owes this member a reverse debt, it **nets the two**: records a `Settlement` for the overlap instead of a split, so mutual debts aren't double-counted. Leftover beyond the reverse debt becomes a normal `ExpenseSplit`. The payer's own share is always a plain split.

`SettlementRequest::withValidator()` enforces that a settlement amount cannot exceed `balanceBetween(debtor, creditor)`.

### Testing

- PHPUnit 10 with SQLite in-memory database (`phpunit.xml` forces `DB_CONNECTION=sqlite`, `:memory:`).
- All tests use `RefreshDatabase`.
- **No model factories are used in tests** (only the stub `UserFactory` exists). Tests create models directly via `::query()->create()`.
- Unit tests isolate services (`DebtLedgerServiceTest`). Feature tests exercise HTTP flow (POST/PUT/DELETE routes, assert redirects, verify DB state): `ExpenseFlowTest`, `ExpenseNettingTest`, `SettlementFlowTest`, `ChoreFlowTest`, `MemberFlowTest`, `ShoppingFlowTest`.
- `ExpenseNettingTest` specifically covers the `netShare` mutual-debt-cancellation path.

### Frontend

- Blade layout: `resources/views/layouts/app.blade.php` is a fixed sidebar layout (Tailwind, dark slate sidebar) that loads `@vite(['resources/css/app.css', 'resources/js/app.js'])`.
- One view per module at `resources/views/{module}/index.blade.php`, plus `resources/views/dashboard.blade.php`.
- Tailwind CSS v4 via `@tailwindcss/vite` plugin. Minimal JS (Axios only, no framework; Alpine is mentioned in the PRD but not currently wired).
- Nav uses `request()->routeIs('module.*')` for active-state highlighting; flash messages via `status`/`error` session keys.

### Localization

Configured for Indonesian (`id`). `config/app.php` sets `locale => 'id'`, `AppServiceProvider::boot()` calls `Carbon::setLocale('id')`, and user-facing strings are translated through `lang/id.json`. Add new strings to `lang/id.json` with an Indonesian translation and wrap them in `__()` in Blade views.

### Database

- MySQL in production, SQLite in-memory for tests.
- Migrations use foreign key constraints (`constrained()`, `cascadeOnDelete()`, `restrictOnDelete()`, `nullOnDelete()`).
- `Member` uses soft deletes (`deleted_at`); form-request `exists` rules add `->whereNull('deleted_at')` so archived members can't be picked as payers/participants.
- The schema has been refactored — the old Bills/SharedExpenses/DebtSettlements/ChoreRotations entities were merged into Expenses/ExpenseSplits/Settlements and single-member chore assignment. Do not reintroduce the old entities.