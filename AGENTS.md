# ATLAS — AI Coding Agent Instructions

## Project Overview

ATLAS is an existing ERP/distribution management application.

- Framework: CodeIgniter 3
- Backend: PHP
- Database: PostgreSQL
- Frontend: Bootstrap, AdminLTE, and JavaScript
- Preserve the existing application architecture and business workflows.

## General Working Rules

1. Inspect the relevant existing files and database structures before proposing changes.
2. Do not redesign working modules or introduce new frameworks without explicit approval.
3. Work on one objective at a time.
4. Explain the proposed changes before implementing them.
5. Wait for explicit approval before modifying code, database structures, or data.
6. Keep modifications minimal and limited to the approved objective.
7. Do not modify unrelated modules.
8. Never silently fix additional issues discovered during an investigation.
9. Report potential problems separately and wait for instructions.

## Development Workflow

Follow this sequence:

**Inspect → Explain → Propose → Approve → Implement → Test → Confirm → NEXT**

For every implementation task:

- Identify the exact file or files involved.
- Explain the current behavior and proposed change.
- Provide the smallest practical modification.
- Describe how to verify the result.
- Wait for the user's confirmation before proceeding to another objective.

A user confirmation such as **🟢** indicates that the current step has passed.

## Coding Conventions

- Respect the repository's `.editorconfig`.
- Preserve existing indentation and formatting in files being edited.
- Use LF line endings, UTF-8 encoding, and a final newline.
- Avoid unrelated formatting changes.
- Prefer plain ES6 JavaScript for application-specific code.
- Do not introduce jQuery dependencies into new application code.
- Follow existing PHP and CodeIgniter conventions.
- Preserve existing naming conventions, including the `Atlas_` prefix where applicable.
- Do not rename existing database tables, columns, classes, or methods without approval.

## Database Safety

- Inspect the actual schema before writing SQL that depends on it.
- Do not assume column names, constraints, or relationships.
- Do not execute database migrations or data-changing SQL without explicit approval.
- Do not modify production data without explicit authorization.
- Distinguish ASUS development/testing from TORIL deployment.
- Never assume a change tested on ASUS has already been deployed to TORIL.

## Financial and Inventory Integrity

Treat these workflows as sensitive:

- Sales Orders and Delivery Receipts
- Sales Invoices and Accounts Receivable
- Customer Payments and Other Deductions
- Sales Returns and Credit Memos
- Reusable Customer Credits
- Purchase Orders and Goods Receiving
- Inventory movements and Stock Ledger

For changes affecting these workflows:

- Trace related calculations and dependent modules.
- Preserve existing transaction and status rules.
- Consider rounding, duplicate posting, reversals, and concurrent operations.
- Do not change accounting or inventory behavior without approval.
- Prefer reproducible tests and reconciliation of expected versus actual results.

## Git and Deployment Safety

- Inspect Git status before editing.
- Do not automatically commit, push, pull, merge, reset, or deploy.
- Do not overwrite unrelated uncommitted changes.
- Never use destructive Git commands without explicit authorization.
- Summarize modified files and verification results after implementation.
- Treat ASUS testing and TORIL deployment as separate approval steps.

## Read-Only Investigations

When asked to investigate or review:

- Read and trace the relevant code.
- Identify exact files, methods, and line numbers.
- Distinguish confirmed bugs from potential risks.
- Provide concrete examples where useful.
- Do not modify files unless explicitly authorized.

## Communication Style

- Be concise, specific, and technically accurate.
- Prefer exact file paths and actionable instructions.
- Do not present assumptions as verified facts.
- Do not overwhelm the user with multiple implementation stages at once.
- Stop at the agreed checkpoint and wait for **🟢 NEXT**.
