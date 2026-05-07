# Sprint 31 Exact Gap List

Date: 2026-05-07

## Screen Gaps And Fixes

| Screen | Old screenshot path | New screenshot path | Severity | Exact visual gap before Sprint 31 | Exact fix applied | File changed |
| --- | --- | --- | --- | --- | --- | --- |
| Home | `present31.png`, `timeline11min.jpg` | `I:/Etamen/.tmp/sprint31-new-screenshots/01-home.png` | High | Home was still inspired-by-old, not old structure: generic hero, too many dashboard actions above fold | Rebuilt first viewport into old teal header, search row, doctor promo banner, appointment card, speciality strip, nearby doctors | `home_experience_widgets.dart`, `home_page.dart`, `app_colors.dart` |
| Bottom nav | `present31.png` | `01-home.png` | Medium | Active tab treatment still looked new Material | Active tab is orange icon/label on white nav, max five tabs preserved | `home_page.dart` |
| Doctor list | `present31.png` | `03-doctors-list.png` | High | Doctor cards lacked old photo block/rating/visual density | Larger media avatar block, orange rating placeholder, chips for fee/location/experience, old-style CTA card | `doctor_card.dart` |
| Doctor profile | `present31.png` | `04-doctor-profile.png` | High | Profile header felt too modern/gradient-heavy and slots were too long | White old-style doctor info card, larger avatar, rating row, grouped chips, about card, compact day/time slot selector | `doctor_profile_page.dart`, `slot_picker.dart` |
| Booking | `present31.png` | `05-booking.png`, `05b-booking-confirm.png` | Critical | Slot picker listed every day and all times, pushing CTA far below fold | Horizontal day selector and current-day time grid with orange selected state | `slot_picker.dart`, `appointment_booking_page.dart` visual dependency |
| Payment methods | `present32.png` | `06-payment-methods.png` | Medium | Flow was safe but looked more backend/status oriented | Consumer cards with visual method icons, friendly payment summary, no raw backend status | Existing Sprint 30 payment files retained |
| Proof upload | `present32.png` | `07-payment-proof-upload.png` | Medium | Proof upload needed a clearer app-like drop/upload area | Polished upload card and friendly manual review copy retained | Existing Sprint 30 payment files retained |
| Account | `present31.png` | `08-account.png` | Low | Account is not a key old Doctor Finder strength | Kept clean white cards and old teal app shell; no feature changes | Existing shell/theme |
| Website landing | `doctor-finder-website-wait.png` | `09-website-landing.png` | High | Sprint 30 landing was close but not old enough in first viewport wording/nav | Old-style nav labels, orange `Join As Doctor +`, big `Find A Doctor!`, search pill, peach/teal split | `etamen-backend/resources/views/welcome.blade.php` |

## Remaining Exact Gaps

- Doctor images are still placeholders; backend needs a safe `avatar_url` or `image_url`.
- Ratings are visual placeholders for near-old card balance; backend needs verified rating/review fields before presenting real ratings.
- Some old app text was English-first; Etamen currently runs Arabic-first, so the clone is visual/structural rather than an exact language clone.
- Website is still a lightweight landing, not the full old public marketing site.
- Status bar/device overlays were visible in real-device screenshots; final store screenshots should be taken with a clean screenshot profile.
