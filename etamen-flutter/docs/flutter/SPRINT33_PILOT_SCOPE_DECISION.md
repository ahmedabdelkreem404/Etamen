# Sprint 33 Pilot Scope Decision

Date: 2026-05-08

## Decision

**Option C: Not ready for any pilot in this Sprint 33 run.**

Reason: the physical Android device verification did not happen. Sprint 33 cannot approve even a doctor-only supervised pilot until the following are tested on a real phone:

- Login.
- Doctor booking.
- Real proof image upload.
- Admin accept of the same proof.
- Flutter payment/appointment refresh after admin review.
- Logout/session restore.

## Pharmacy / Lab Scope

Pharmacy and lab flows were **not physically tested** in Sprint 33. They should remain **internal QA only** until a real-device pass proves at least the basic order path.

If the physical doctor/payment gate later passes but pharmacy/lab still have not passed, the recommended first pilot scope should become:

**Option B: First pilot includes doctors + payments only. Pharmacy/labs remain internal QA.**

## Current Scope Matrix

| Area | Sprint 33 result | Pilot scope impact |
| --- | --- | --- |
| Doctors + booking | Not tested on physical device | Cannot approve pilot yet. |
| Manual payment proof upload | Not tested on physical device | Blocker. |
| Admin payment review | Not tested against phone-created proof | Blocker. |
| Appointment state after admin review | Not tested | Blocker. |
| Logout/session restore | Not tested on physical device | Blocker. |
| Pharmacy | Not tested on physical device | Scope out until tested. |
| Labs | Not tested on physical device | Scope out until tested. |
| Health/AI/notifications smoke | Not tested on physical device | Should not block doctor-only pilot unless navigation/session/security breaks. |

## Exact Next Scope Gate

Run a physical-device pass for doctors + manual payment first. If it passes, choose between:

- **Option A** only if pharmacy and lab order basics also pass on the same or equivalent physical device.
- **Option B** if doctors + payments pass but pharmacy/labs are not ready.
- **Option C** if proof upload, admin review, or logout/session restore fails.
