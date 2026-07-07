Section 1 — Project Philosophy

Principles
  • One objective at a time.
  • No surprise refactoring.
  • Protect working architecture.
  • Component first, abstraction later.
  • Let patterns emerge naturally.

Section 2 — Standard Master Module
  application/
  │
  ├── controllers/
  │      Users.php
  │      Salesmen.php
  │      Products.php
  │
  ├── models/
  │      User_model.php
  │      Salesman_model.php
  │
  ├── views/
  │      users/
  │      salesmen/
  │
  └── assets/js/modules/
        users.js
        salesmen.js

Section 3 — Naming Convention
  Buttons
    btnNewUser
    btnSaveUser
    btnNewSalesman
    btnSaveSalesman

  Forms
    frmUser
    frmSalesman

  Textboxes
    txtUsername
    txtSalesmanCode

  Hidden IDs
    hidUserId
    hidSalesmanId

  Checkboxes
    chkUser1
    chkSalesman1

Section 4 — Controller Flow
  Every save() follows this order:
  1. Read ID
  2. Business Rule Checks
  3. Form Validation
  4. Prepare Common Data
  5. Create
        • password
        • entered_by
        • entered_on
  6. Update
        • updated_by
        • updated_on
  7. Return JSON

Section 5 — Model API
  Every master model exposes the same methods.
    getAll()
    get($id)
    save($data, $id = null)
    activate($id)
    deactivate($id)
    codeExists(...)

Section 6 — Shared Partials
  page_header.php
  toolbar.php
  search_toolbar.php

Section 7 — Atlas Configuration
  Application
  Company
  Security
  Localization
  UI

Section 8 — Things We Intentionally Deferred
  This is important.
    Future
    MY_Model
    Atlas.selection
    loadTable()
    Permissions
    Roles
    Audit Trail Viewer
  Notice these aren't forgotten—they're consciously postponed.