# Provider Operations Runbook

This runbook covers the local Flutter provider workspace MVP.

## Workspace

1. Login as provider owner or staff.
2. Open Account.
3. Choose provider workspace from workspace switcher.
4. Open dashboard and allowed quick actions.

## Dashboards

- Provider dashboard shows summary cards and backend-provided quick actions.
- Unknown quick actions must show a safe "later" message, not crash.
- Backend remains authoritative for permissions.

## Provider Pages

- Doctor: appointments and safe appointment details.
- Hospital: hospital-context appointments, departments, linked doctors.
- Radiology: orders and order details without raw result paths.
- Pharmacy/Lab: conservative MVP read-only pages where applicable.
- Gym: bookings, plans, and classes.
- Coach: bookings, availability, session types, and packages.

## Limited Staff

- Limited staff can open allowed read-only lists.
- Manage buttons should be hidden when unavailable.
- If an action is forced, backend must return `403` and UI should show a friendly message.

## Provider Support

- Provider can create/view scoped provider support tickets.
- Provider must not see admin internal notes or another provider's tickets.

## Never Show

- Raw proof paths.
- Raw result paths.
- Raw prescription paths.
- Private provider document paths.
- Internal contracts.
- Admin-only notes.
