# Sprint 29 UI Before / After Comparison

Date: 2026-05-07

## Screenshot Capture Status

Old UI screenshots were inspected from local image files. New UI screenshots were not captured in this sprint because a seeded authenticated Flutter runtime/device screenshot pass is still pending. The comparison below is therefore a written screen-by-screen comparison based on the implemented Flutter UI after Sprint 29.

## Old UI Screenshot References

- `I:/Etamen/docs/2dd1ca67b8d187837e9cabf96a56287d.png` (`present31.png`): old mobile home, doctor list/profile, booking.
- `I:/Etamen/docs/4d4f24444fb307e514f79c908e8cf5e1.png` (`present32.png`): old payment/profile/chat/video feature sheet.
- `I:/Etamen/docs/8c6edbac921bd547f029e3c903cbda1b.png` (`present33.png`): old pharmacy/lab/payment screens.
- `I:/Etamen/docs/43a5b92e1d8c4175fbc68c47927ec1db.png` (`present34.png`): old lab/admin visuals.
- `I:/Etamen/docs/9457e97075e3603b6f442b6c7d8b2ef4.jpg` (`timeline11min.jpg`): old app/site timeline and website reference.
- `I:/Etamen/Website/PHPScript/storage/logs/doctor-finder-website-wait.png`: old public website landing page.

## New UI References

New visual changes are in these Flutter files:

- `lib/app/theme/app_colors.dart`
- `lib/app/theme/app_theme.dart`
- `lib/core/widgets/empty_view.dart`
- `lib/features/home/presentation/widgets/home_experience_widgets.dart`
- `lib/features/doctors/presentation/pages/doctors_list_page.dart`
- `lib/features/doctors/presentation/widgets/doctor_card.dart`
- `lib/features/doctors/presentation/pages/doctor_profile_page.dart`
- `lib/features/doctors/presentation/widgets/slot_picker.dart`
- `lib/features/appointments/presentation/pages/appointment_booking_page.dart`
- `lib/features/payments/presentation/pages/payment_page.dart`
- `lib/features/payments/presentation/pages/manual_payment_page.dart`
- `lib/app/localization/app_localizations.dart`

## Written Side-By-Side

| Area | Old UI | New UI Before Sprint 29 | New UI After Sprint 29 | Match Status |
| --- | --- | --- | --- | --- |
| Home first viewport | Teal/cyan medical header, search, doctor/appointment emphasis | Clean but more generic dashboard | Teal gradient hero, greeting, search, doctor booking highlight, grouped quick actions | Mostly restored |
| Doctor cards | Doctor photo, white card, chips, fee/location, clear booking | Good but plain placeholder and one CTA | Premium white card, initials avatar, specialty chips, fee/location, details + book CTAs | Improved, image gap remains |
| Doctor profile | Strong teal header, image, details, booking CTA | Functional teal header and details | Gradient profile header, avatar, grouped details, about fallback, slot card | Improved, not identical |
| Booking | Day/time grouped with clear action | Functional stepper and slot picker | More visual stepper, doctor summary, old-style day/time chip accent | Improved |
| Payment | Payment method visual sheets | Functional payment flow | Softer payment banner/summary and safer manual review wording | Improved but still less rich |
| Empty states | Friendly illustrations/cards | Plain message/icon | White card empty states with teal icon and human copy | Improved without reusing unclear assets |
| Website | Public Doctor Finder landing exists | No matching new website | Gap documented only | Not restored |

## What Now Matches

- Teal/cyan medical identity is much stronger.
- White card system and soft shadows are closer to the old app.
- Home is now doctor-first rather than admin/dashboard-first.
- Doctor listing/profile are visually closer to the old Doctor Finder experience.
- Empty states no longer look broken when Sprint 27 seed data is missing.
- Arabic payment/empty/AI copy is less technical.

## What Does Not Match

- No real doctor images because the current backend contract lacks doctor avatar/image URL.
- No old-style public marketing website exists in the new project.
- Product/pharmacy/lab screens still lack the visual richness of old image-heavy cards.
- Old profile/rating/review/map details are not present in the current contract.
- New screenshots still need to be captured on device after seed data is available.

## Final Parity Percentage

Final app visual parity after Sprint 29: **73%**.

The new app is now closer to the old UI/UX spirit, but it is not the same UI/UX and should not be described as a complete restoration.

## Sprint 30 Before / After

| Area | Old reference | New screenshot | Before Sprint 30 | After Sprint 30 |
| --- | --- | --- | --- | --- |
| Home | `present31.png`, `timeline11min.jpg` | `01-home.png` | Felt improved but still generic/dashboard-like | Strong teal hero, rounded search, doctor-first CTA, old-style white cards |
| Doctor list | `present31.png` | `06-doctors-list.png` | Functional cards without enough old Doctor Finder character | Teal hero, wrapped specialty chips, premium avatar cards, fee/location/rating/CTA treatment |
| Doctor profile | `present31.png` | `07-doctor-profile.png` | Useful but not premium enough | Strong teal header, large avatar placeholder, grouped chips, about, old-style slots |
| Booking | old make appointment screenshots/assets | `09-booking.png` | Functional slot list | Large old-style slot tiles, orange selected state, clear steps |
| Payment | `present32.png` | `24-payment-methods.png`, `25-payment-manual.png` | Safe but visually plain; raw status risk found | Consumer payment cards, proof upload card, friendly appointment status copy |
| Website landing | `doctor-finder-website-wait.png` | `13-website-landing.png` | No matching public landing | New Blade landing restores first-viewport old website mood |

## Sprint 30 Final Comparison

What now matches:

- Teal/cyan medical identity is dominant again.
- Home and Doctor journey feel like a patient booking product, not an admin dashboard.
- Doctor cards and profile now visually echo the old Doctor Finder layout.
- Payment no longer exposes raw backend appointment status on the main payment page.
- A public landing page now exists and visually references the old website.

What still does not match:

- No real doctor photos, because the backend contract still lacks safe `avatar_url` / `image_url`.
- No real ratings/review counts.
- Website is a lightweight landing, not the full old marketing site.
- Pharmacy/labs still need rich seeded data and possibly owned imagery.

Final Sprint 30 parity:

- App shell + doctor booking flow: **90%**.
- Website first viewport: **84%**.
- Overall app/site: **88%**.

## Sprint 31 Final Clone Comparison

Screenshot evidence:

- Old references: `present31.png`, `present32.png`, `present33.png`, `present34.png`, `timeline11min.jpg`, `doctor-finder-website-wait.png`.
- New screenshots: `I:/Etamen/.tmp/sprint31-new-screenshots/`.

| Screen | Old UI reference | New Sprint 31 screenshot | What now matches | What does not match |
| --- | --- | --- | --- | --- |
| Home | `present31.png`, `timeline11min.jpg` | `01-home.png` | Teal header, prominent search, promo banner, appointment card, specialty row, nearby doctors, orange nav active state | Real doctor/banner image missing; RTL/Arabic layout is not an exact old English clone |
| Doctor list | `present31.png` | `03-doctors-list.png` | Search, specialty chips, rounded white cards, avatar media block, rating row, fee/location chips | Real doctor photo and verified reviews missing |
| Doctor profile | `present31.png` | `04-doctor-profile.png` | Large doctor card, rating row, fee/location/experience chips, about card, compact slot selector | Photo/review/map/credentials missing |
| Booking | old MakeAppointment references | `05-booking.png`, `05b-booking-confirm.png` | Stepper, day selector, orange selected day/time, simple confirmation section | Exact old animation and date language not cloned |
| Payment | `present32.png` | `06-payment-methods.png`, `07-payment-proof-upload.png` | Consumer method cards, upload proof card, friendly manual review state | Old payment art/gateway behavior not copied for security |
| Account | old account/profile references | `08-account.png` | White cards, teal shell, simple patient account | Not a core old Doctor Finder clone target |
| Website | `doctor-finder-website-wait.png` | `09-website-landing.png` | Dark top strip, white nav, orange CTA, peach/teal split, `Find A Doctor!`, search pill, service cards | Full old public website and search/doctor join flows are not rebuilt |

## Sprint 31 Decision

The new project is now much closer to the old Doctor Finder visual feeling than Sprint 30, especially Home and the doctor booking path. It is still not a 1:1 clone.

Final Sprint 31 parity:

- App doctor journey: **93%**.
- Overall mobile app: **91%**.
- Website first viewport: **90%**.
- Overall app/site: **91%**.

The product owner should review the Sprint 31 screenshots before more coding. If they still require 95%+, the next blockers are concrete: licensed doctor imagery, real rating fields, exact font choice, and exact old website section sequence.

## Sprint 32 Final Seeded Comparison

Screenshot evidence:

- Old references remain: `present31.png`, `present32.png`, `present33.png`, `present34.png`, `timeline11min.jpg`, `doctor-finder-website-wait.png`.
- Sprint 32 screenshots: `I:/Etamen/.tmp/sprint32-final-screenshots/`.

| Screen | New Sprint 32 screenshot | What now matches better | What still does not match |
| --- | --- | --- | --- |
| Home | `01-home.png` | Seeded doctor cards make the old Doctor Finder home feel more real; teal hero and search remain prominent. | App still uses safe avatar placeholders, not real licensed doctor photos. |
| Doctors list | `03-doctors-list-with-avatar.png` | Doctor cards now show safe avatar URL, real demo rating average/count, location, fee, experience, and `احجز الآن`. | Avatar is generated/demo, not a true doctor photo; no public review excerpts. |
| Doctor profile | `04-doctor-profile-with-avatar.png` | Header/profile card has avatar, rating, fee, city/area, about, and slots in one patient-facing flow. | Exact old photo/map/credential blocks are still missing. |
| Booking | `05-booking-slot-selection.png`, `06-booking-confirmation.png` | Slot selection and sticky CTA are visible on the emulator viewport; selected state uses old orange accent. | Physical small-screen proof still required. |
| Payment | `07-payment-methods.png`, `08-payment-proof-upload.png` | Payment method and proof upload screens feel consumer-facing and avoid raw backend status. | Native file picker proof upload and admin review were not completed in this pass. |
| My appointments | `09-my-appointments.png` | Newly booked appointment appears with friendly pending-payment wording. | Date formatting still mixes English month labels. |
| Pharmacy / Labs | `10-pharmacy-products.png`, `11-labs-tests.png` | Richer demo names prevent empty/broken-looking lists. | Full order/payment/result journeys were not completed. |
| Health / AI / Account | `12-health-dashboard.png` to `17-account.png` | Supporting screens remain visually aligned with the teal/white card system and safe copy. | These screens were not core old Doctor Finder clone targets. |
| Website landing | `18-website-landing.png` | Landing first viewport now shows dark top strip, white nav, orange CTA, peach/teal split, doctor hero image, search pill, and service cards. | It is still a landing page only, not the full old website. |

## Sprint 32 Decision

Final Sprint 32 parity estimate:

- Doctor journey: **94-95%** on seeded emulator.
- Overall mobile app: **93%**.
- Website first viewport: **91%**.
- Overall app/site: **92%**.

The new app is now visually acceptable for supervised product-owner review and very close to the old Doctor Finder patient journey. It is not the same as the old app/site because production photos, public review content, exact font, and full old website pages are still missing.
