# Dashboard-First UX Redesign for Maximum Adoption

**Date:** 2026-06-15
**Status:** Approved
**Goal:** Maximize daily engagement and adoption by making the dashboard the "home base" that pulls housemates back with clear attention items, visual progress, and quick actions.

## Context

The Shared House Manager has 6 complete modules (Housemates, Bills, Cash Fund, Debts, Chores, Shopping) but the dashboard is a static summary that gives no sense of priority, ownership, or actionability. Housemates forget to check the app because nothing pulls them back. The UI is functional but plain — no visual feedback, no quick actions, no engagement hooks.

**Target users:** 2-5 close friends sharing one house, accessing the app equally from mobile and desktop.

**No-auth context:** The app has no user accounts or authentication. All housemates share the same view — there is no "current user" concept. Attention items show everything relevant to the household, not personalized per person. Quick action modals show all housemates/options rather than filtering by "you."

## Design Overview

Redesign the dashboard into **3 zones**, plus navigation badges and a bug fix:

1. **Attention Zone** — "What needs my eyes right now?" (priority-sorted actionable items)
2. **At a Glance** — Visual snapshot cards with progress bars and color-coded status
3. **Quick Actions + Activity Timeline** — One-tap common tasks and a social-feel activity feed

```
┌─────────────────────────────────────────────┐
│  🏠 Attention Zone (priority items)          │
│  🔴 Sopan hasn't paid Listrik — 3 days ago  │
│  🟡 Your chore "Bersihkan Dapur" due tomorrow│
│  🔵 Rina settled her debt to you            │
├─────────────────────────────────────────────┤
│  📊 At a Glance (snapshot cards)             │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐        │
│  │ Bills   │ │ Debts   │ │ Chores  │        │
│  │ ████░░░ │ │ +100k   │ │ 3/5 ✓  │        │
│  └─────────┘ └─────────┘ └─────────┘        │
│  ┌─────────┐ ┌─────────┐                    │
│  │ Cash    │ │ Shopping│                    │
│  │ 350k    │ │ 🔴2 🟡2 │                    │
│  └─────────┘ └─────────┘                    │
├─────────────────────────────────────────────┤
│  ⚡ Quick Actions                           │
│  [+ Bill] [+ Expense] [+ Shopping]           │
│  [✓ Chore Done] [💰 Settle Debt]            │
├─────────────────────────────────────────────┤
│  📋 Activity Timeline                       │
│  [RA] Rina paid Listrik bill 💰 — 2h ago    │
│  [BU] Budi completed Bersihkan Dapur ✅ — y │
│  [AN] Anda added Detergent 🛒 — 3h ago      │
└─────────────────────────────────────────────┘
```

## Zone 1: Attention Items

### Data Source

New method `DashboardService::attentionItems()` queries across all modules and returns a prioritized, deduplicated Collection. No new database tables.

### Attention Item Structure

Each item has:
- `type`: `bill_overdue` | `bill_due_soon` | `chore_due` | `debt_settlement` | `cash_fund_low` | `shopping_high_priority`
- `priority`: `overdue` | `due_soon` | `info`
- `message`: Human-readable string, e.g. "Sopan belum bayar Listrik — jatuh tempo 3 hari lalu"
- `action_url`: Route to the relevant module page
- `action_label`: Verb for the action button, e.g. "Tandai Lunas"
- `housemate_id`: Optional, links to the relevant housemate

### Priority Triggers

| Trigger | Priority | Example Message |
|---------|----------|-----------------|
| Bill participant unpaid past due date | overdue | "Sopan belum bayar Listrik — jatuh tempo 3 hari lalu" |
| Chore assignment due tomorrow | due_soon | "Giliran Anda: Bersihkan Dapur — besok" |
| New debt settlement received | info | "Rina menyelesaikan utang Rp 50.000 ke Anda" |
| Cash fund balance below threshold | due_soon | "Saldo kas Rp 15.000 — rendah" |
| Shopping items with High priority unpurchased | due_soon | "3 barang prioritas tinggi belum dibeli" |
| Bill due this week | info | "Tagihan Internet jatuh tempo 4 hari lagi" |

### Rendering

- Overdue items: Red left border, red badge
- Due soon items: Amber left border, amber badge
- Info items: Blue left border, blue badge
- Sorted by priority (overdue → due_soon → info), then by date within each priority
- Max 5 items shown; "Lihat semua" link if more exist

## Zone 2: At a Glance Cards

### Bills Card

- Shows: "X dari Y tagihan bulan ini sudah dibayar"
- Progress bar: width = (paid / total) * 100%, color shifts red → amber → green
- Tap to navigate to `/bills`

### Debts Card

- Shows: Total outstanding — "Total utang: Rp 150.000" with breakdown
- Two-line: "Piutang (orang lain utang Anda): Rp 100k / Utang (Anda utang orang lain): Rp 50k"
- Tap to navigate to `/debts`

### Cash Fund Card

- Shows: Current balance with trend indicator (↑ or ↓ this month)
- "Saldo: Rp 350.000 ↑" (if contributions > expenses this month)
- Tap to navigate to `/cash-fund`

### Chores Card

- Shows: Weekly completion rate — "3/5 tugas selesai minggu ini"
- Progress bar: X/Y chores completed
- Tap to navigate to `/chores`

### Shopping Card

- Shows: Priority breakdown — "🔴 2 tinggi · 🟡 2 sedang · 🔵 1 rendah"
- Total unpurchased count
- Tap to navigate to `/shopping`

### Card Layout

- Desktop: 3 columns (bills, debts, chores on top; cash fund, shopping on bottom)
- Tablet: 2 columns
- Mobile: 1 column, stacked

## Zone 3: Quick Actions

### Action Buttons

| Action | Modal Form Fields | Auto-Defaults |
|--------|-------------------|---------------|
| + Bill | Type (dropdown), Amount, Housemates (checkboxes) | Current month, all active housemates checked |
| + Shared Expense | Payer (dropdown), Amount, Participants (checkboxes) | All active housemates pre-selected as participants |
| + Shopping Item | Name, Priority (dropdown) | Medium priority default |
| ✓ Mark Chore Done | Checklist of due chores with assignee names | Shows all chores due this week, grouped by assignee |
| 💰 Settle Debt | Debtor (dropdown), Creditor (dropdown), Amount | Shows all outstanding debt pairs |

### Implementation

- Modals use Alpine.js `x-data="{ open: false }"` with `x-show` for toggle
- Forms submit via standard POST to existing routes
- After submission: flash success message, redirect back to dashboard
- No AJAX required for v1

## Zone 4: Activity Timeline

### Rendering

Each activity item shows:
```
[Colored circle with initials] [Name] [action verb] [subject] [emoji] — [time ago]
```

- Housemate avatar: Colored circle with first two letters of name, deterministic color from name hash
- Action verbs: Indonesian, e.g. "membayar", "menyelesaikan", "menambahkan", "menyelesaikan"
- Emojis by module: 💰 bills/payments, ✅ chores, 🟡 debts, 🛒 shopping, 💵 cash fund
- Time ago: Carbon `diffForHumans()` with `id` locale ("2 jam yang lalu", "kemarin")

### Data Source

Existing `DashboardService::summary()` already returns `recentActivities`. Enhance the rendering in the Blade view with the new format.

## Navigation Badges

Add count badges to sidebar navigation items:

- **Bills**: Count of unpaid bill participants for current month
- **Debts**: Count of outstanding debt pairs (non-zero balances)
- **Chores**: Count of chore assignments due this week
- **Shopping**: Count of unpurchased high-priority items

Implementation: `AppServiceProvider` or a view composer that injects badge counts into all views.

## Bug Fix: Debts View Housemate Names

`resources/views/debts/index.blade.php` ~line 101 displays "Housemate #:id" instead of the housemate's name. Fix by eager-loading the housemate relationship and accessing `->housemate->name`.

## Technical Details

### Frontend Stack

- **Alpine.js**: For modal toggle and interactive UI state (lightweight, Laravel-ecosystem standard)
- **Tailwind CSS v4**: Existing utility classes, no new CSS framework
- **Heroicons**: SVG inline icons (Laravel-compatible, no icon font needed)
- **No new JS frameworks**: Keep existing Blade + minimal JS architecture

### Backend Changes

- `DashboardService::attentionItems()`: New method that queries across modules. Cash fund threshold is a class constant (`CASH_FUND_LOW_THRESHOLD = 50000`, i.e. Rp 50.000)
- `DashboardService::snapshotCards()`: New method returning enriched card data (progress, net balance, completion rates)
- View composer: Inject nav badge counts into all views
- Existing `DashboardService::summary()`: Minor adjustments to support new rendering

### Localization

All new UI strings use `__()` and are added to `lang/id.json`:
- Attention item messages
- Quick action labels
- Progress bar labels ("2 dari 5 selesai")
- Card labels and trend indicators
- Time-ago strings handled by Carbon's `id` locale

### Testing

- Unit test `DashboardServiceTest::test_attention_items_returns_prioritized_list`
- Unit test `DashboardServiceTest::test_snapshot_cards_return_progress_data`
- Feature test for dashboard view rendering with new zones
- Feature test for quick action modal form submissions

## Scope Boundaries (YAGNI)

These are explicitly **excluded** from this design:

- Authentication / user accounts
- Push notifications / email reminders
- Data export / PDF reports
- Multi-household support
- Recurring bills automation
- Real-time WebSockets
- Any new database tables or migrations
- API endpoints

## Files Affected

| File | Change |
|------|--------|
| `app/Services/DashboardService.php` | Add `attentionItems()`, `snapshotCards()`, modify `summary()` |
| `app/Providers/AppServiceProvider.php` | Add view composer for nav badge counts |
| `resources/views/dashboard.blade.php` | Complete rewrite with 3-zone layout |
| `resources/views/layouts/app.blade.php` | Add nav badge counts, include Alpine.js CDN |
| `resources/views/debts/index.blade.php` | Fix housemate name display bug |
| `resources/views/components/` | New Blade components: `attention-item`, `snapshot-card`, `quick-action-modal`, `activity-item` |
| `lang/id.json` | Add all new translation strings |
| `resources/css/app.css` | Minor additions for progress bars and avatar circles |
| `tests/Unit/DashboardServiceTest.php` | New test file |
| `tests/Feature/DashboardViewTest.php` | New test file |