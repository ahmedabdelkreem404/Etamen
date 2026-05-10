# Sprint 50 - Unified Workspace + Provider Dashboard Foundation

Status: local foundation only.

This sprint adds a backend-owned workspace contract and a minimal provider dashboard foundation so one authenticated user can safely see the workspaces that belong to them.

This is not a production/staging approval and it is not a complete provider portal.

## Backend Workspace Contract

Endpoint:

- `GET /api/v1/me/workspaces`

Authentication:

- Sanctum authenticated users only.

Response includes:

- safe user summary
- `default_workspace`
- `available_workspaces`

Workspace types:

- `patient`
- `provider`
- `platform_admin`

Rules:

- Every authenticated user gets a patient workspace.
- Provider workspaces are returned only for active provider staff/owners.
- Provider workspaces hide private documents, contracts, bank data, and payment config.
- Platform admin workspace appears only for platform admin/super admin users.
- Suspended or non-operational providers are not exposed as operational dashboards.

## Provider Permission Foundation

Provider staff now has an optional `permissions` JSON column.

Compatibility rules:

- Existing `owner`, `admin`, and `staff` roles still work.
- Owner receives the full provider permission set automatically.
- Admin receives a safe admin default set when no custom permissions exist.
- Staff receives a limited default set when no custom permissions exist.
- Custom permissions are validated against `ProviderPermission`.

## Provider Dashboard API

Endpoint:

- `GET /api/v1/provider/workspace/{provider}/dashboard`

Rules:

- Requires active staff membership for the same provider.
- Rejects staff from other providers.
- Quick actions are filtered by backend permissions.
- No patient medical details, proof files, result files, private documents, contracts, or payment config are exposed.

Dashboard response includes provider summary, role, permissions, counts, summary cards, recent items, and permission-filtered quick actions.

Provider-type summaries added:

- doctor appointments and slots
- hospital departments and linked doctors
- radiology orders and scans
- pharmacy orders/products
- lab orders/catalog
- gym bookings/plans/classes
- coach sessions/availability/packages

## Staff Management Foundation

Endpoints:

- `GET /api/v1/provider/workspace/{provider}/staff`
- `POST /api/v1/provider/workspace/{provider}/staff`
- `PATCH /api/v1/provider/workspace/{provider}/staff/{staff}`
- `DELETE /api/v1/provider/workspace/{provider}/staff/{staff}`

Rules:

- Requires owner or `manage_staff`.
- Staff must belong to the same provider.
- Owner cannot be deleted or edited through these APIs.
- Owner role cannot be granted through the API.
- Non-owner cannot grant admin role.
- This local foundation only supports adding existing users by email. Invitation flow is deferred.

## Local Seed Data

Pilot demo data now includes patient-only, provider owner, platform admin, and limited staff accounts for local QA.

All seeded credentials are fake local/demo credentials only and must not be used for production.

## Flutter Scope

Flutter now consumes:

- `/me/workspaces`
- `/provider/workspace/{provider}/dashboard`

Flutter includes:

- account workspace section
- workspace switcher
- provider dashboard shell
- platform admin shell
- selected workspace persistence and logout cleanup

Flutter does not invent permissions. The backend remains the source of truth.

## Local QA

Screenshot path:

- `I:/Etamen/.tmp/sprint50-local-workspaces/`

APK path:

- `I:/Etamen/.tmp/etamen-local-workspace-provider-dashboard.apk`
- `C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-workspace-provider-dashboard.apk`

Emulator note:

- The local emulator was slow on cold app starts and showed Android cold-start ANR prompts during automated account switching.
- Choosing `Wait` allowed the app to continue and valid screenshots were recaptured after the app finished loading.
- This is an emulator resource issue observed during QA, not a staging or phone result.

## What Is Not Implemented

- Full provider operational pages.
- Provider invitation emails.
- Platform admin replacement for Filament.
- Staging deployment.
- Real-phone proof.
- Production readiness.

## Final Local Decision

Local decision after tests/build/QA passed:

```text
LOCAL_WORKSPACE_PROVIDER_DASHBOARD_ACCEPTED
```
