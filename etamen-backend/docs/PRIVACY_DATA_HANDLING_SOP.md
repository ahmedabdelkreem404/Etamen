# Privacy and Data Handling SOP

This SOP defines local pilot guardrails for patient, provider, and platform admin data. It is not a final legal privacy policy.

## Private Paths and Files

- Never expose raw payment proof paths.
- Never expose raw radiology or lab result paths.
- Never expose raw prescription image paths.
- Never expose provider private document paths.
- Never expose national ID, tax, commercial, or bank document paths.
- API responses may return safe metadata such as existence, file name, MIME type, size, and upload date when appropriate.

## Notes and Support Content

- Internal admin notes must be visible to platform admins only.
- Patient/provider support ticket responses must not include another user's content.
- Provider-related tickets must be scoped to the provider and authorized staff only.
- Medical support messages must not diagnose, prescribe, or interpret clinical results.

## Provider and Staff Isolation

- Provider staff may access only their assigned provider workspace.
- Permission checks are backend-owned.
- Flutter may hide unavailable actions for UX only; it must not be trusted for authorization.
- Wrong-provider access must return `403`.
- Limited staff must not manage staff, payments, or operational actions without explicit backend permissions.

## Audit Log

- Audit log payloads must use safe summaries.
- Audit events must not include secrets, raw paths, payment configs, or private documents.
- Admin actions for payment, provider approval, support, refund, and dispute decisions must be auditable.

## Future TODOs

- Legal data retention policy.
- User data export workflow.
- User deletion/anonymization workflow.
- Server backup and restore testing.
- Production incident response process.
- Formal DPA/privacy review before real user data.
