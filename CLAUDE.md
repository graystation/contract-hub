# CLAUDE.md

# Contract Hub

## Overview

Contract Hub is a CRM and contract management system for a small corporation.

The purpose of this project is to manage:

- Customers
- Projects
- Contracts
- Invoices
- Payments

The system should remain simple, maintainable, and easy to operate by a small business owner.

This project is intended for real-world business operations and long-term maintenance.

---

## Business Purpose

This system is NOT an electronic signature platform.

This system IS a business management platform.

Primary goals:

- Customer management
- Project management
- Contract management
- Invoice management
- Payment tracking
- Contract evidence storage
- Audit logging

Out of scope (do not implement):

- PKI infrastructure
- Certificate authorities
- Digital certificate management
- Legal-grade electronic signature services
- Blockchain-based contract systems
- Complex cryptographic frameworks
- Google Drive integration
- Accounting software integration (freee, MayCloud, etc.)
- Credit card / payment processing
- External electronic contract services (CloudSign, etc.)
- Complex identity verification

The goal is a practical and legally reasonable business workflow system.

Electronic consent and audit logging are sufficient.

---

## Implementation Status

### Completed

- Authentication (Laravel Breeze)
- Companies CRUD
- Projects CRUD
- Contracts CRUD
- Contract PDF generation (dompdf)
- Contract PDF storage + SHA256 hash
- Hash verification
- Electronic consent URL (sign_token, 14-day expiry)
- External consent form (no login required)
- Consent request email (ContractSignRequestMail)
- Contract-signed notification email (ContractSignedNotificationMail)
- Invoices CRUD (auto-numbering INV-YYYY-NNNN, 10% tax calculation)
- Payments CRUD (status auto-update based on paid amount)
- Invoice model accessors (paid_amount, unpaid_amount, is_overdue, is_due_soon, etc.)
- Invoice PDF generation + SHA256 hash storage
- Invoice PDF download
- Invoice send email with PDF attachment (InvoiceSendMail)
- Dashboard (all-time + monthly billing stats, unpaid invoice list)
- AuditLog for all major operations
- fmt_amount() helper

### Not Yet Implemented

- Production SMTP configuration
- Google Drive integration
- Accounting software export
- Invoice PDF hash verification (similar to contract hash verify)
- Bulk invoice operations
- Contract renewal reminders
- Automated backup scripts

---

## Target Users

Primary user:

- Small business owner
- Consultant
- Website operator
- Service provider

The UI should prioritize usability and operational efficiency.

---

## Tech Stack

- Laravel 13
- PHP 8.4
- MySQL
- Blade
- Tailwind CSS
- Laravel Breeze
- barryvdh/laravel-dompdf

Do NOT introduce:

- React
- Next.js
- Inertia
- Livewire
- Vue

Server-side rendering should be used throughout the application.

Keep the architecture simple.

---

## Database

Use MySQL.

Do not use SQLite.

Expected data:

- Companies
- Projects
- Contracts
- ContractFiles
- Invoices
- Payments
- InvoiceFiles
- AuditLogs

The system is expected to become a long-term business asset.

Prioritize maintainability and scalability.

---

## Coding Rules

Follow Laravel conventions.

Use:

- FormRequest validation
- Eloquent relationships
- Service classes
- Resource Controllers
- Model accessors for computed properties

Avoid:

- Fat Controllers
- Business logic inside Blade templates
- Raw SQL when Eloquent is appropriate
- Inline computation in views (use model accessors instead)

Code comments must be written in English.

Prefer readable code over clever code.

**Before adding significant new features:**

1. Run `php artisan test` — all tests must pass
2. Add Feature tests for the new functionality
3. Do not break existing tests

---

## UI Rules

Desktop-first design.

Primary users are operating from a desktop browser.

Focus on:

- Readability
- Simplicity
- Fast operation

Avoid:

- Fancy animations
- Dashboard gimmicks
- Overly complex interactions

Business software should feel stable and predictable.

---

## Data Model Summary

### Companies → Projects → Contracts → ContractFiles
### Projects → Invoices → Payments
### Projects → Invoices → InvoiceFiles
### All operations → AuditLogs

---

## Storage

PDF files are stored in two locations:

- `storage/app/contracts/` — contract PDFs
- `storage/app/invoices/` — invoice PDFs

Both are configured as named disks in `config/filesystems.php`.

These directories must be included in any backup strategy.

---

## Mail

All mail uses Laravel Mail.

For development: `MAIL_MAILER=log` (output to `storage/logs/laravel.log`)

For production: configure SMTP in `.env`

Admin notification email: `CONTRACT_ADMIN_EMAIL` in `.env`

---

## Design Philosophy

This project should feel like a practical business tool.

Do not over-engineer.

Do not build features that are not immediately useful.

Favor:

- Reliability
- Simplicity
- Maintainability
- Operational efficiency

Every feature should solve a real business problem.

The project should remain understandable and maintainable five years from now.
