# Admin Operations API

The Admin Operations API is a local Platform Admin operations foundation. It is not a public API and does not replace Filament.

## Authorization

All `/api/v1/admin/operations/*` routes require an authenticated platform admin or super admin user. Patient, provider owner, provider staff, and limited staff users must receive `403`.

Flutter may hide admin screens for usability, but backend authorization is the source of truth.

## Dashboard

```text
GET /api/v1/admin/operations/dashboard
```

Returns safe counts for:

- pending payment reviews
- pending provider approvals
- open support tickets
- open refund requests
- unresolved disputes
- today's appointments
- today's radiology orders
- today's gym bookings
- today's coach bookings
- recent safe events
- quick actions

## Payment Reviews

```text
GET /api/v1/admin/operations/payments/pending
GET /api/v1/admin/operations/payments/{payment}
POST /api/v1/admin/operations/payments/{payment}/accept
POST /api/v1/admin/operations/payments/{payment}/reject
```

Reject requires a reason. Proof data is metadata-only and never includes raw private storage paths.

## Provider Approvals

```text
GET /api/v1/admin/operations/providers/pending
GET /api/v1/admin/operations/providers/{provider}
POST /api/v1/admin/operations/providers/{provider}/approve
POST /api/v1/admin/operations/providers/{provider}/reject
POST /api/v1/admin/operations/providers/{provider}/suspend
```

Reject and suspend require a reason. Provider document payloads expose checklist metadata only.

## Support

```text
GET /api/v1/admin/operations/support/tickets
GET /api/v1/admin/operations/support/tickets/{ticket}
POST /api/v1/admin/operations/support/tickets/{ticket}/reply
POST /api/v1/admin/operations/support/tickets/{ticket}/internal-note
POST /api/v1/admin/operations/support/tickets/{ticket}/assign
POST /api/v1/admin/operations/support/tickets/{ticket}/close
```

Internal notes are admin-only.

## Refunds

```text
GET /api/v1/admin/operations/refunds
GET /api/v1/admin/operations/refunds/{refund}
POST /api/v1/admin/operations/refunds/{refund}/mark-under-review
POST /api/v1/admin/operations/refunds/{refund}/approve
POST /api/v1/admin/operations/refunds/{refund}/reject
POST /api/v1/admin/operations/refunds/{refund}/mark-processed
```

This is a manual operations foundation. It does not send money through a payment gateway.

## Disputes

```text
GET /api/v1/admin/operations/disputes
GET /api/v1/admin/operations/disputes/{dispute}
POST /api/v1/admin/operations/disputes/{dispute}/assign
POST /api/v1/admin/operations/disputes/{dispute}/resolve
POST /api/v1/admin/operations/disputes/{dispute}/close
```

Admin actions require notes where applicable.

## Audit Log

```text
GET /api/v1/admin/operations/audit-log
```

Audit payloads include safe actor and entity summaries only.

## Privacy Rules

Responses must not expose:

- `.env`
- `APP_KEY`
- database secrets
- Paymob secrets
- payment config
- raw proof paths
- raw result paths
- raw prescription image paths
- provider private document paths
- national ID, tax, commercial, or bank document paths
- internal contracts
- patient medical private records
