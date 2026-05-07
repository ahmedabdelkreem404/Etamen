# Sprint 30 Visual Gap Report

Date: 2026-05-07

Baseline from Sprint 29: approximately 73% visual parity overall.

## Screen Gaps

| Screen | Old reference | New screenshot | Gap before | Fix applied in Sprint 30 | Gap after |
| --- | --- | --- | ---: | --- | ---: |
| Home | `present31.png`, `timeline11min.jpg` | `01-home.png` | 6/10 | Rebuilt first viewport with teal hero, large search, doctor-first CTA, old-style white cards, badge, calmer AI CTA. | 1.5/10 |
| App shell / bottom nav | `flutter_01.png`, old app timeline | `01-home.png` | 5/10 | Replaced Material navigation treatment with five old-style icon-led tabs and teal active state. | 2/10 |
| Doctor list | `present31.png`, `timeline11min.jpg` | `06-doctors-list.png` | 6/10 | Added teal doctor hero, prominent search, wrapped specialty chips, premium doctor cards with avatar area, rating placeholder, fee/location chips, CTAs. | 1.5/10 |
| Doctor profile | `present31.png` | `07-doctor-profile.png` | 6/10 | Strong teal profile header, large avatar placeholder, rating row, fee/location/experience chips, grouped about and slots. | 1.8/10 |
| Booking | `present31.png`, `makeAppointmentScreenImages/*` | `09-booking.png` | 5/10 | Larger slot/day cards, orange selected state, simple step indicator, clearer patient wording. | 2/10 |
| Payment | `present32.png` | `24-payment-methods.png`, `25-payment-manual.png` | 5/10 | Consumer-grade method cards, distinct Vodafone/InstaPay visuals, polished proof upload, removed raw `pending_payment` copy. | 2.5/10 |
| Account | `present32.png` | `04-account-tab.png` | 4/10 | Shell/theme improvements apply; full old profile clone was not in scope. | 3/10 |
| Website landing | `doctor-finder-website-wait.png` | `13-website-landing.png` | 10/10 | Recreated lightweight Laravel landing: dark strip, white nav, orange CTA, split peach/teal hero, rounded search, old medical visual. | 2.5/10 |

## Remaining Visual Gaps

- Doctor images are placeholders because the current Doctor contract does not provide image/avatar URLs.
- Ratings are visual placeholders because backend ratings/review counts are not available.
- Booking day labels still use English date abbreviations from current date formatting.
- Website landing is visually inspired by the old site but is not a full public marketing website/CMS.
- Pharmacy/labs screens still need richer real data before they can feel as polished as old screenshots.

## Honest App Parity After Sprint 30

- App shell + doctor-booking flow: about 90%.
- Overall mobile app including non-doctor areas: about 86%.
- Website landing: about 84%.
- Full product parity with old app/site: not 100%.
