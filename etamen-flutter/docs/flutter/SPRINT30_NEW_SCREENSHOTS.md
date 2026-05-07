# Sprint 30 New Screenshots

Date: 2026-05-07

Screenshots were captured from the physical Android device `Infinix X657C` using package `com.etamen.etamen_app`. A temporary accidental switch to the old package `doctor.finder.videocalling` was detected and those mixed screenshots were not used as current-new-app evidence.

## Captured New App Screenshots

Saved under `I:/Etamen/.tmp/sprint30-new-screenshots/`.

| Screen | Screenshot |
| --- | --- |
| Home | `01-home.png` |
| Services tab | `02-services-tab.png` |
| Health tab | `03-health-tab.png` |
| Account tab | `04-account-tab.png` |
| Appointments tab | `05-appointments-tab.png` |
| Doctors list | `06-doctors-list.png` |
| Doctor profile | `07-doctor-profile.png` |
| Doctor profile scrolled / slots | `08-doctor-profile-scrolled.png` |
| Booking | `09-booking.png` |
| Payment methods | `24-payment-methods.png` |
| Manual payment / proof upload | `25-payment-manual.png` |
| New website landing | `13-website-landing.png` |

## Notes

- Doctors, profile, booking, payment methods, and manual payment were captured after the final Sprint 30 visual fixes.
- Payment was reached through the demo booking flow; this created a demo pending-payment appointment in the local/dev environment.
- Pharmacy and Labs were not individually deep-captured in this pass; the Services tab was captured and the Sprint 30 code did not add new pharmacy/lab business behavior.
- The old app package remained installed on the device. Future screenshot sessions should explicitly verify `mCurrentFocus` is `com.etamen.etamen_app`.
