# Sprint 50 - Local Workspace + Provider Dashboard QA

Status: local emulator QA only.

API base:

- `http://10.0.2.2:8000/api/v1`

APK:

- `I:/Etamen/.tmp/etamen-local-workspace-provider-dashboard.apk`
- `C:/Users/Ahmed Abdelkareem/OneDrive/Desktop/Etamen_Android_Website_Ready/etamen-local-workspace-provider-dashboard.apk`

Screenshots:

- `I:/Etamen/.tmp/sprint50-local-workspaces/`

## Flutter Screens Added

- Account workspace section.
- Workspace switcher bottom sheet.
- Provider dashboard shell.
- Platform admin dashboard shell.

## APIs Consumed

- `GET /me/workspaces`
- `GET /provider/workspace/{provider}/dashboard`

## QA Results

Patient-only account:

- Login works.
- Patient home opens.
- Account page shows workspace section.
- Workspace switcher shows patient workspace.
- Logout clears selected workspace.

Provider owner accounts:

- Doctor dashboard loads.
- Hospital dashboard loads.
- Radiology dashboard loads.
- Gym dashboard loads.
- Fitness coach dashboard loads.
- Provider type label appears correctly.
- Quick actions are displayed from backend response.
- Unavailable quick actions show safe placeholder text.

Staff account:

- Limited staff dashboard loads.
- Staff sees only limited permission badges.
- Staff quick actions are filtered.

## Screenshots Captured

- `01-patient-home.png`
- `02-account-workspace-section.png`
- `03-workspace-switcher.png`
- `04-provider-dashboard-doctor.png`
- `05-provider-dashboard-hospital.png`
- `06-provider-dashboard-radiology.png`
- `07-provider-dashboard-gym.png`
- `08-provider-dashboard-coach.png`
- `09-staff-limited-dashboard.png`
- `10-switch-back-to-patient.png`
- `11-logout.png`

## Emulator Note

The emulator showed Android cold-start ANR prompts while automated QA was clearing and relaunching the app repeatedly. Selecting `Wait` allowed the app to continue. Valid screenshots were recaptured after the app became responsive.

This is not a physical phone result.

## Not Implemented

- Full provider operational pages.
- Real provider staff invitation flow.
- Flutter Filament replacement.
- Staging deployment.
- Real-phone QA.

## Decision

Decision after tests/build and emulator QA passed:

```text
LOCAL_WORKSPACE_PROVIDER_DASHBOARD_ACCEPTED
```
