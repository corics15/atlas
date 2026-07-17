# Atlas ERP - Business Rules

> Last Updated: July 2026

---

# Core Principles

1. One business responsibility per method.
2. Documents do not update unrelated modules directly.
3. Inventory is never edited manually. Inventory is the result of business transactions.
4. Every inventory movement must have a source document.
5. Business rules first. User Interface second.

---

# Purchase Order Lifecycle

OPEN
→ Nothing received.

PARTIAL
→ Some quantities received.

COMPLETED
→ All ordered quantities received.

CLOSED
→ Manually closed by an authorized user.

CANCELLED
→ Cancelled before completion.

Rules

- Multiple Goods Receipts may exist for one Purchase Order.
- A completed Purchase Order cannot receive additional goods.
- Once the first Goods Receipt exists, Purchase Order line items are considered structurally locked.
- Product substitutions require either:
    - a revised Purchase Order (future)
    - a new Purchase Order

---

# Goods Receipt

Responsibilities

- Save GRN Header
- Save GRN Details
- Update Purchase Order received quantity
- Update Purchase Order status
- Notify Inventory Engine

Goods Receipt does NOT

- Modify Product master data directly.
- Update Accounting directly.

---

# Inventory Engine

Single entry point for all inventory movements.

Incoming

- Goods Receipt
- Customer Return
- Opening Balance

Outgoing

- Sales Invoice
- Supplier Return
- Stock Transfer
- Stock Adjustment

Inventory Engine responsibilities

- Update qty_on_hand
- Write Stock Ledger

---

# Stock Ledger

Append only.

Never edited.

Never deleted.

Purpose

- Inventory audit trail
- Inventory history
- Cost history
- Document traceability

Transaction Types

- GRN
- SALE
- RETURN
- TRANSFER
- ADJUSTMENT
- OPENING
- COUNT

---

# Product Master

Stores

- Barcode
- Description
- UOM
- Supplier
- Cost
- Selling Price
- qty_on_hand

Does NOT store

- Batch Number
- Expiry Date
- Unit Cost History
- Stock Movement History

---

# Future Modules

Purchasing
✔ Complete

Inventory
In Progress

Sales
Planned

Accounts Receivable
Planned

Accounts Payable
Planned

General Ledger
Planned