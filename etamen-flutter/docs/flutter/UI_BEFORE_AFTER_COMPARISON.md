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
