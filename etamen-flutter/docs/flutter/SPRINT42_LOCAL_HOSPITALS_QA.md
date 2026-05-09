# Sprint 42 - Local Hospitals Flutter QA

Date: 2026-05-09

Scope: local Android emulator only. No staging, no Hostinger, no public launch claim.

Backend URL used by APK:

```text
http://10.0.2.2:8000/api/v1
```

APK:

```text
I:\Etamen\.tmp\etamen-local-hospital-section.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-hospital-section.apk
```

SHA-256:

```text
65A43A36CD376A3D655EAC49F82DB421F2328FFE6AA719E87C8D481D712D81A6
```

Screenshots:

```text
I:\Etamen\.tmp\sprint42-local-hospitals\
```

## Flutter Screens Added

New patient-facing hospital feature:

- `HospitalsPage`
- `HospitalDetailsPage`
- `HospitalDepartmentDoctorsPage`
- `HospitalCard`
- `HospitalInfoHeader`
- `LocationSummaryCard`
- `HospitalDepartmentCard`
- `HospitalCapabilityBadges`

Navigation:

- Hospitals entry added inside the existing Services tab.
- Bottom navigation remains five tabs.
- Hospital doctor cards reuse the existing doctor booking/profile path.

## Emulator Walkthrough

| Step | Result | Screenshot |
| --- | --- | --- |
| Login/session | PASS | `01-home.png` |
| Services tab shows hospital entry | PASS | `02-services-hospitals-entry.png` |
| Hospitals list | PASS | `03-hospitals-list.png` |
| Hospital details | PASS | `04-hospital-details.png` |
| Departments visible | PASS | `05-hospital-departments.png` |
| Department doctors | PASS | `06-department-doctors.png` |
| Hospital doctor profile | PASS | `07-hospital-doctor-profile.png` |
| Slot selection / booking screen | PASS | `08-booking-slot.png` |
| Payment methods reached | PASS | `09-payment-methods.png` |
| My appointments shows created booking | PASS | `10-my-appointments.png` |
| Logout returns to login | PASS | `11-logout.png` |

## Notes

- Booking from hospital currently books the selected doctor through the existing appointment flow.
- Payment proof/admin review was not repeated in Sprint 42 because Sprint 41 already accepted the full local proof/admin status update gate.
- No raw backend statuses or private storage paths were observed in the hospital flow.
- `url_launcher` behavior for opening maps was not expanded in this sprint; the hospital details page safely shows address and coordinates.

## Decision

Flutter hospital section is locally accepted.

Verification:

```text
flutter pub get
dart format .
flutter analyze
flutter test
flutter build apk --debug --target-platform android-x64 --dart-define=ETAMEN_API_BASE_URL=http://10.0.2.2:8000/api/v1 --dart-define=ETAMEN_ENV=local
```

Result: PASS.
