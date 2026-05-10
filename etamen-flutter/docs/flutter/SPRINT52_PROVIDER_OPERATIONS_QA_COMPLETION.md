# Sprint 52 - Local Provider Operations QA Completion

Sprint 52 completed the missing local emulator QA for the Sprint 51 provider operations Flutter gate.

This is local-only against:

```text
http://10.0.2.2:8000/api/v1
```

It does not approve staging, Hostinger, public launch, real-phone readiness, or a complete provider portal.

## What Blocked Sprint 51

Sprint 51 implemented the generic provider operation pages, but only the doctor path was tested deeply on emulator.

The remaining provider screenshots were missing for hospital, radiology, pharmacy, lab, gym, coach, and limited staff blocked-action behavior.

## QA And Polish Fixes

- Fixed provider workspace switching from the account bottom sheet by using the parent navigation context after closing the modal.
- Added coach packages quick action mapping so the backend `packages` quick action opens the real Flutter operation page.
- Added the `coach/packages` operation section.
- Fixed Arabic mojibake in the coach packages page title.
- Fixed Arabic mojibake in hospital booking context labels.
- Rebuilt and installed the local APK, then re-captured the coach packages screen from the fixed build.

## Emulator QA

Screenshot root:

```text
I:\Etamen\.tmp\sprint52-provider-operations-qa\
```

Provider screenshots:

- `doctor\01-doctor-workspace-switcher.png` through `doctor\05-doctor-action-or-readonly-state.png`
- `hospital\01-hospital-workspace-switcher.png` through `hospital\05-hospital-doctors.png`
- `radiology\01-radiology-workspace-switcher.png` through `radiology\05-radiology-action-or-readonly-state.png`
- `pharmacy\01-pharmacy-workspace-switcher.png` through `pharmacy\05-pharmacy-order-details-or-empty.png`
- `lab\01-lab-workspace-switcher.png` through `lab\05-lab-order-details-or-empty.png`
- `gym\01-gym-workspace-switcher.png` through `gym\07-gym-action-or-readonly-state.png`
- `coach\01-coach-workspace-switcher.png` through `coach\08-coach-action-or-readonly-state.png`
- `staff\01-staff-workspace-switcher.png` through `staff\05-staff-wrong-provider-blocked.png`

## Per-Provider Result

Doctor:

- workspace switcher, dashboard, appointment list, appointment details, and safe action state passed.

Hospital:

- workspace switcher, dashboard, hospital-context appointments, departments, and linked doctors passed.

Radiology:

- workspace switcher, dashboard, orders list, order details, and safe action state passed.

Pharmacy:

- workspace switcher, dashboard, read-only orders, products, and empty/details state passed.

Lab:

- workspace switcher, dashboard, orders, catalog, and details state passed.

Gym:

- workspace switcher, dashboard, bookings, booking details, plans, classes, and safe action state passed.

Coach:

- workspace switcher, dashboard, bookings, booking details, availability, session types, packages, and safe action state passed.

Limited staff:

- limited dashboard showed only backend-provided permissions.
- allowed read-only list opened.
- manage actions were not exposed.
- wrong-provider access was verified by API as forbidden.

## Tests And Build

- `flutter pub get`: PASS
- `dart format .`: PASS
- `flutter analyze`: PASS
- `flutter test`: PASS, 187 tests
- `flutter build apk --debug --target-platform android-x64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local`: PASS

APK:

```text
I:\Etamen\.tmp\etamen-local-provider-operations-qa.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-provider-operations-qa.apk
```

## Security And Privacy

No patient-facing or provider-facing API response used in this sprint exposed:

- raw proof paths
- raw result paths
- raw prescription image paths
- raw file URLs
- payment config
- Paymob secrets
- private provider documents
- national ID, tax, commercial, or bank documents
- internal contract terms
- other-provider data

## Remaining Scope

- staging not approved
- real phone not approved
- provider portal not complete
- pharmacy and lab remain read-only provider MVP pages
- result upload UI deferred

## Final Decision

`LOCAL_PROVIDER_OPERATIONS_MVP_ACCEPTED`

