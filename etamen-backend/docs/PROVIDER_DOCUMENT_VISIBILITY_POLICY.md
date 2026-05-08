# Provider Document Visibility Policy

Date: 2026-05-08  
Sprint: 36 - Unified Provider Platform Foundation

## Purpose

Provider documents are required for verification, but many are sensitive. Sprint 36 adds explicit document visibility rules so Etamen can support more provider types without exposing private files.

## Document Types

Supported values:
- `national_id`
- `medical_license`
- `syndicate_card`
- `certificate`
- `tax_card`
- `commercial_register`
- `facility_license`
- `gym_license`
- `coach_certificate`
- `radiology_license`
- `lab_license`
- `pharmacy_license`
- `license` legacy compatibility
- `other`

## Visibility Values

`admin_only`:
- visible to platform admin and provider owner only.
- never shown to patients.
- never exposed as raw storage path.

`public_certificate`:
- can appear publicly only after admin approval.
- exposes safe metadata only.
- does not expose private storage paths.

## Forced Private Documents

These are always forced to `admin_only`:
- `national_id`
- `tax_card`
- `commercial_register`

Bank documents are not a Sprint 36 document type, but if added later they must also be admin-only.

## Public Certificate Rules

A public certificate must be:
- approved by admin.
- marked as `public_certificate`.
- stored without exposing raw private path.
- returned only as safe metadata:
  - document id
  - document type
  - approved timestamp
  - file original name/mime/size

Rejected documents never appear publicly.

## Storage Rule

Provider documents are stored as private files. Sprint 36 does not make provider document files public. Public certificate metadata is separate from file download access.

## Audit Rule

Document approval/rejection is audited through `audit_logs`:
- `provider_document.approved`
- `provider_document.rejected`

## Remaining Work

Future work:
- signed admin-only download links.
- provider document checklist per provider type.
- expiry dates for licenses/certificates.
- renewal reminders.
- virus scanning before production.

