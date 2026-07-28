### question
from Sales Order List, i select a SO No. and click Create Invoice, the NOTICE is displayed,
so that means this Sales Order is fully invoiced and cannot be edited? just briefly answer this question.

### answer
No.
The notice only means:
✖ You cannot create ANOTHER Sales Invoice
because there are no remaining quantities.
It does NOT mean the Sales Order is editable.
The Sales Order is still:
POSTED
and therefore:

✖ Edit
✖ Cancel (in our current design)
✔ Print
✔ View

The notice is only blocking this action:
Sales Order
      │
Create Invoice
      │
✖ No remaining quantities

So the Sales Order remains POSTED and locked. It simply cannot produce any more invoices until an existing invoice is cancelled or its quantities are reduced.

#### SAMPLE SCENARIOS
## Scenario 1 — Fully Invoiced
Sales Order
--------------------
SO-000001

Rice        100
Sugar        50

Status = POSTED

↓

Create Invoice

SI-000001

Rice        100
Sugar        50

Status = OPEN

↓

POST Invoice

Inventory ↓

Stock Ledger ↓

SI Status = POSTED

↓

User clicks:

Sales Order List

SO-000001

Create Invoice

↓

Result

NOTICE

This Sales Order has already been fully invoiced.

No remaining quantities are available for invoicing.

## Scenario 2 — Partial Invoice
Sales Order

Rice        100

Status = POSTED

↓

Invoice #1

Rice         60

OPEN

↓

POST

↓

Remaining

40

↓

Create Invoice

↓

Sales Invoice opens

Rice         40

## Scenario 3 — Cancel Invoice
Sales Order

Rice        100

↓

Invoice #1

100

↓

Cancel Invoice

↓

Remaining

100

↓

Create Invoice

↓

Allowed again

Rice        100

## Scenario 4 — Edit OPEN Invoice
Invoice

100

↓

Edit

Change Qty

100 → 80

↓

Save

↓

Remaining

20

↓

Create Invoice

↓

Allowed

Rice        20
## Scenario 5 — POSTED Invoice
Invoice

Status = POSTED

↓

✖ Edit

✖ Cancel

✔ Print
Final Flow
Sales Order

OPEN
   │
POST
   │
POSTED
   │
Create Invoice
   │
Sales Invoice

OPEN
   │
├── Edit
├── Cancel
├── Print
└── POST
     │
     ▼
POSTED
     │
├── Print
└── Collection (later)