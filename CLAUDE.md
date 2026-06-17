# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Docker

All `php artisan`, `./vendor/bin/pint`, and `npm` commands must run inside the Docker container. **Always use `docker exec -it shared-house-manager-app`** — never run these directly on the host.

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
docker exec -it shared-house-manager-app php artisan test tests/Unit/ChoreAssignmentServiceTest.php

# Run a specific test method
docker exec -it shared-house-manager-app php artisan test --filter test_assignments_rotate_in_order

# Run unit tests only
docker exec -it shared-house-manager-app php artisan test --testsuite=Unit

# Run feature tests only
docker exec -it shared-house-manager-app php artisan test --testsuite=Feature

# Lint with Laravel Pint
docker exec -it shared-house-manager-app ./vendor/bin/pint

# Start dev server
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

A Laravel 10 monolith for managing shared household expenses, chores, and shopping. No user accounts — housemates are simple database records. The frontend uses Blade templates with Tailwind CSS v4 (via Vite).

### Module Structure

Six feature modules, each with a consistent pattern:

| Module | Controller | Service(s) | Routes |
|--------|-----------|------------|--------|
| Housemates | `HousemateController` | — | CRUD + archive |
| Bills | `BillController` | `MoneySplitService` | Create, list, mark paid |
| Debts | `DebtController` | `DebtLedgerService`, `MoneySplitService` | Shared expenses, settlements |
| Chores | `ChoreController` | `ChoreAssignmentService` | Create, rotate, complete |
| Shopping | `ShoppingController` | — | Add items, mark purchased |
| Dashboard | `DashboardController` (invokable) | `DashboardService` | Aggregated summary |

### Data Flow

```
Route (web.php) → Controller → Service (business logic) → Model (Eloquent)
                                                      → ActivityLogService (audit trail)
Controller returns view() with data
```

- **Controllers** are thin — they validate via FormRequest, delegate to services, log activity, and return `back()` redirects.
- **Services** contain business logic (splitting money, computing balances, rotating chores). They are plain PHP classes resolved via Laravel's auto-injection.
- **FormRequests** (`app/Http/Requests/`) define validation rules. All authorize to `true` (no auth).
- **ActivityLogService** is injected into every controller action that mutates data. It logs to the `activity_logs` table with polymorphic subject references.

### Key Models

- **Housemate** — soft-archived via `archived_at` timestamp. `scopeActive()` filters to non-archived.
- **Bill** — has many `BillParticipant` (share per housemate) and `BillPayment` (actual payments).
- **SharedExpense** — tracks one housemate paying for a group. Generates debts via `SharedExpenseParticipant`.
- **DebtSettlement** — records partial/full debt repayments.
- **Chore** — has `ChoreRotation` (ordered list of housemates) and `ChoreAssignment` (weekly assignments generated ahead).
- **ShoppingItem** — has priority (High/Medium/Low) and purchase records.
- **ActivityLog** — polymorphic audit trail with `subject_type`/`subject_id` and JSON `metadata`.

### Money Split Logic

`MoneySplitService::splitEvenly()` distributes an integer amount across N participants, handling remainders by giving +1 to the first R participants (where R = amount % N). This is used by both Bills and Shared Expenses.

### Testing

- PHPUnit 10 with SQLite in-memory database.
- All tests use `RefreshDatabase` trait.
- Unit tests test services in isolation (create models directly).
- Feature tests test HTTP flow (POST routes, assert redirects, verify DB state).
- No factories are used — tests create models with `::query()->create()` directly.

### Frontend

- Blade layouts with `@yield('content')` sections.
- Tailwind CSS v4 via `@tailwindcss/vite` plugin.
- Minimal JS (bootstrap.js for Axios, no framework).
- Views live in `resources/views/{module}/index.blade.php`.
- Shared layout in `resources/views/layouts/app.blade.php` with nav and flash messages.

### Localization

The application is configured for Indonesian (`id`). `config/app.php` sets `locale => 'id'`, `AppServiceProvider` calls `Carbon::setLocale('id')`, and user-facing strings are translated through `lang/id.json`. Add new strings to `lang/id.json` with an Indonesian translation and wrap them in `__()` in Blade views.

### Database

- MySQL in production, SQLite in-memory for tests.
- Migrations use foreign key constraints (`constrained()`, `cascadeOnDelete()`, `restrictOnDelete()`, `nullOnDelete()`).
- Receipt uploads are stored on the `public` disk under `storage/app/public/receipts`. Run `php artisan storage:link` so the `public/storage` symlink serves them.
