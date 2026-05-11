# Provider Operations Runbook

This runbook covers the local provider workspace and provider operations MVP.

## Workspace Switcher

1. Login with a provider owner or staff account.
2. Open Account.
3. Open workspace switcher.
4. Select the provider workspace.
5. Confirm provider dashboard loads.

## Provider Dashboard

- Shows provider name, type, role, permissions, summary counts, and allowed quick actions.
- Quick actions are returned by backend and filtered by permissions.
- Flutter may hide unavailable actions, but backend authorization is final.

## Doctor Operations

- Open appointments.
- Review safe patient summary, appointment status, and payment status.
- Confirm/complete/cancel only when backend allows the action.
- Do not write diagnosis or treatment notes in support tickets.

## Hospital Operations

- View hospital-context appointments.
- View departments and linked doctors.
- Do not mix direct doctor appointments with hospital-context bookings.

## Radiology Operations

- View radiology orders and order details.
- Move order status only when backend permission and status allow it.
- Do not expose raw result paths.
- Do not interpret radiology results medically.

## Pharmacy and Lab MVP

- Pharmacy and lab pages are conservative/read-only where lifecycle actions are not fully accepted.
- Do not expose prescription image paths or lab result paths.
- Do not claim full payment/lifecycle readiness if not tested.

## Gym Operations

- View gym bookings, plans, and classes.
- Manage booking status only when backend allows.
- Do not expose payment proof paths.

## Coach Operations

- View coach bookings, availability, session types, and packages.
- Avoid treatment or nutrition prescription claims.
- Keep patient goals private and scoped.

## Limited Staff

- Limited staff sees only assigned provider workspace.
- Limited staff may view allowed pages.
- Manage actions must be hidden or return `403`.
- Staff cannot access another provider's data.

## Provider Support Ticket Flow

- Provider can create or view provider-related tickets where scoped.
- Internal admin notes are not visible to provider users.
- Provider must not see another provider's ticket.

## Provider Must Not See

- Raw proof paths.
- Raw result or prescription paths.
- National ID, tax, commercial, or bank private document paths.
- Internal contracts.
- Admin-only notes.
- Other provider data.
