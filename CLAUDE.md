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

Out of scope:

- PKI infrastructure
- Certificate authorities
- Digital certificate management
- Legal-grade electronic signature services
- Blockchain-based contract systems
- Complex cryptographic frameworks

The goal is to create a practical and legally reasonable business workflow system.

Electronic consent and audit logging are sufficient.

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

- Laravel 12
- PHP 8.4
- MySQL
- Blade
- Tailwind CSS
- Laravel Breeze

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
- Invoices
- Payments
- Audit Logs
- Contract Files

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

Avoid:

- Fat Controllers
- Business logic inside Blade templates
- Raw SQL when Eloquent is appropriate

Code comments must be written in English.

Prefer readable code over clever code.

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

## Data Model

### Companies

Represents customers and business partners.

Fields:

- company_name
- contact_name
- email
- phone
- address
- notes

---

### Projects

Represents individual engagements.

Examples:

- Website advertisement
- Consulting engagement
- AI implementation support

Fields:

- company_id
- title
- type
- description
- status
- started_at
- ended_at

Project types:

- advertisement
- consulting
- other

---

### Contracts

Represents agreements related to projects.

Fields:

- project_id
- contract_number
- contract_type
- status
- signed_at
- notes

Status:

- draft
- sent
- signed
- cancelled

---

### Invoices

Represents invoices issued to customers.

Fields:

- project_id
- invoice_number
- amount
- issued_at
- due_date
- status

Status:

- unpaid
- paid

---

### Payments

Represents incoming payments.

Fields:

- invoice_id
- amount
- paid_at
- memo

---

### Audit Logs

Represents important system events.

Examples:

- Contract created
- Contract signed
- Invoice issued
- Payment recorded

Fields:

- user_id
- action
- target_type
- target_id
- ip_address
- user_agent
- created_at

---

## Contract Evidence Strategy

When a contract is signed:

Store:

- Contract PDF
- SHA256 hash
- Audit log entry
- Signed timestamp
- Email address
- IP address
- User agent

Generate an evidence package.

Example:

contracts/CON-2026-001/

- contract.pdf
- contract.sha256
- audit.json
- signed_at.txt

The purpose is evidence preservation and operational safety.

---

## Backup Strategy

The system should support automated backups.

Important contract data should be stored in multiple locations.

Examples:

- Local storage
- Cloud storage
- Email notification

The objective is resilience against accidental deletion and server failure.

---

## Development Roadmap

### Phase 1

Implement:

- Authentication
- Companies
- Projects
- Contracts
- Dashboard

Do not implement later phases yet.

---

### Phase 2

Implement:

- PDF generation
- Contract sharing links
- Electronic consent
- Audit logs

---

### Phase 3

Implement:

- Invoices
- Payments
- Revenue tracking

---

### Phase 4

Implement:

- Evidence packages
- Backup automation
- Contract renewal reminders

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
