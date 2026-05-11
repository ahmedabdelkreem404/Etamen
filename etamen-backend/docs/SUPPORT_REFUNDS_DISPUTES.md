# Support, Refunds, and Disputes Foundation

Sprint 58 added a local foundation for support tickets, refund requests, and disputes.

This is an operations foundation only. It does not provide legal approval, live refund gateway integration, public support SOP approval, or production readiness.

## Support Tickets

Tables:

- `support_tickets`
- `support_ticket_messages`

Patient/provider APIs:

- `POST /api/v1/support/tickets`
- `GET /api/v1/support/tickets`
- `GET /api/v1/support/tickets/{ticket}`
- `POST /api/v1/support/tickets/{ticket}/messages`

Admin APIs:

- `GET /api/v1/admin/operations/support/tickets`
- `GET /api/v1/admin/operations/support/tickets/{ticket}`
- `POST /api/v1/admin/operations/support/tickets/{ticket}/reply`
- `POST /api/v1/admin/operations/support/tickets/{ticket}/internal-note`
- `POST /api/v1/admin/operations/support/tickets/{ticket}/assign`
- `POST /api/v1/admin/operations/support/tickets/{ticket}/close`

Rules:

- users see only their own tickets
- provider staff can see provider-related tickets only
- admins can see all tickets
- internal notes are admin-only
- no medical diagnosis or treatment templates are included

## Refund Requests

Table:

- `refund_requests`

Patient APIs:

- `POST /api/v1/refunds`
- `GET /api/v1/refunds`
- `GET /api/v1/refunds/{refund}`

Admin APIs:

- `GET /api/v1/admin/operations/refunds`
- `GET /api/v1/admin/operations/refunds/{refund}`
- `POST /api/v1/admin/operations/refunds/{refund}/mark-under-review`
- `POST /api/v1/admin/operations/refunds/{refund}/approve`
- `POST /api/v1/admin/operations/refunds/{refund}/reject`
- `POST /api/v1/admin/operations/refunds/{refund}/mark-processed`

Refund statuses:

- `requested`
- `under_review`
- `approved`
- `rejected`
- `processed`
- `cancelled`

Approved means a platform decision only. Processed means a manual/admin confirmation. No live refund gateway integration was added.

## Disputes

Table:

- `disputes`

Patient APIs:

- `POST /api/v1/disputes`
- `GET /api/v1/disputes`
- `GET /api/v1/disputes/{dispute}`

Admin APIs:

- `GET /api/v1/admin/operations/disputes`
- `GET /api/v1/admin/operations/disputes/{dispute}`
- `POST /api/v1/admin/operations/disputes/{dispute}/assign`
- `POST /api/v1/admin/operations/disputes/{dispute}/resolve`
- `POST /api/v1/admin/operations/disputes/{dispute}/close`

Dispute statuses:

- `open`
- `investigating`
- `waiting_user`
- `waiting_provider`
- `resolved`
- `rejected`
- `closed`

## Local QA Status

Backend and Flutter automated tests passed. Emulator visual QA remains blocked by an app ANR during login automation and must be rerun before Sprint 58 can be accepted.
# Sprint 59 QA Status

Sprint 59 verified the local support, refund, and dispute foundation through emulator QA.

Verified:

- patient support ticket creation and details
- provider support ticket form from provider workspace context
- admin support ticket list/details/internal note flow
- patient refund request form and admin refund details/actions
- patient dispute form and admin dispute details/resolve flow
- admin internal notes are not exposed to patient/provider responses

Evidence:

```text
I:\Etamen\.tmp\sprint59-local-admin-operations-qa\
```
