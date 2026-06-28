# SPEC-001 – Shared House Manager
## Method

### System Architecture

```text
┌─────────────────────────────┐
│        Web Browser          │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│      Laravel Blade UI       │
│      Tailwind CSS v4        │
│      Alpine.js (Optional)   │
└──────────────┬──────────────┘
               │
               ▼
┌─────────────────────────────┐
│      Laravel 10 MVC         │
│                             │
│ • Bill Module               │
│ • Debt Module               │
│ • Chore Module              │
│ • Shopping Module           │
│ • Dashboard Module          │
└─────────────────────────────┘

```

### Application Modules

#### Dashboard

Displays:

* Current month bills
* Unpaid bill summary
* Outstanding debts
* Current chore assignments
* Shopping list status
* Recent activities

#### Housemates Module

Since there are no user accounts, housemates are simply records.

Example:

| Name  |
| ----- |
| Mahdy |
| A     |
| B     |
| C     |

Functions:

* Add housemate
* Edit housemate
* Archive housemate

#### Bill Management Module

Bill Types:

* Electricity
* Water
* Internet
* Gas
* Custom

Features:

* Create bill
* Select participating housemates
* Automatic split
* Mark member payment status
* Monthly history

Example:

```text
Internet Bill
Rp300,000

Participants:
Mahdy
A
B
C
D

Result:
Rp60,000 each
```

#### Debt Tracking Module

Tracks personal expenses paid by one resident for multiple residents.

Example:

```text
Mahdy pays Rp40,000

Participants:
Mahdy
A
B
C
```

System computes:

```text
Share = Rp10,000
```

Debt Ledger:

| Debtor | Creditor | Amount |
| ------ | -------- | ------ |
| A      | Mahdy    | 10,000 |
| B      | Mahdy    | 10,000 |
| C      | Mahdy    | 10,000 |

Features:

* Create shared expense
* Automatic debt generation
* Settlement recording
* Outstanding balance report

#### Chore Management Module

Features:

* Create chore
* Assign to a member
* Mark complete
* View history

Example chores:

```text
Clean Bathroom
Take Out Trash
Sweep Living Room
Clean Kitchen
```

Rotation: _(removed — chores are assigned to a single member, not rotated)_

#### Shopping List Module

Features:

* Add item
* Set priority
* Mark purchased
* View purchase history

Priority Levels:

```text
High
Medium
Low
```
