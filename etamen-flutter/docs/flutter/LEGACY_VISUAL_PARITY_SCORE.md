# Sprint 29 Legacy Visual Parity Score

Date: 2026-05-07

The new app is closer to the old Doctor Finder feeling after Sprint 29, but it is not identical and should not be described as 100% parity.

| Area | Old UI Reference Quality | New UI Current Quality | Parity % | Missing Pieces | Fix Applied In Sprint 29 | Remaining Gap |
| --- | --- | --- | ---: | --- | --- | --- |
| Color palette | Strong teal/cyan medical identity with white cards | Teal/cyan theme now stronger and less dark-green heavy | 84% | Exact old template color balance | Updated `AppColors` and theme surfaces | Minor color tuning after screenshots |
| Typography | Clean mobile template typography, likely Poppins-style | Flutter default text system, improved weights/hierarchy | 70% | Exact old font family and template rhythm | Stronger heading/body weights | Consider product-owner-approved font later |
| Home screen | Very polished doctor-first home with hero/search/banner | Teal hero, search, doctor booking highlight, grouped services | 76% | Real doctor image/banner and old appointment visual density | Rebuilt first viewport around old-style hero and doctor booking | Needs real data screenshot review |
| Bottom navigation / shell | Simple 5-tab app shell | 5-tab NavigationBar with softer theme | 76% | Exact old icon/label styling | Kept max 5 tabs and softer nav background | Verify real device density |
| Doctor cards | Strong doctor photos, specialty/location/fee/booking CTA | Premium white cards, initials avatar, chips, details/book CTAs | 76% | Doctor image/avatar URL, ratings, next available slot summary | Reworked `DoctorCard` and documented avatar URL gap | Backend contract needed for photos |
| Doctor profile | Strong teal profile header and grouped details | Teal gradient header, avatar, fee/location/experience, about, slots | 74% | Real image, rating/reviews, map/rich address, richer bottom CTA | Reworked profile hero and about fallback | Needs richer backend data |
| Booking flow | Clear old day/time slot selection and bottom CTA | Stepper, doctor summary, clean grouped slots, safe CTA | 70% | Exact old slot artwork/bottom sheet polish | Improved step indicator, summary, slot styling | Needs seeded available slots and device QA |
| Payment flow | Old payment sheets/gateway options felt visual and consumer-grade | Safer summary/banner/manual proof flow | 65% | Rich payment method sheet, real method branding | Softened payment summary and manual review wording | Needs real payment methods and device proof-upload test |
| Empty states | Old app used friendly illustrations | Icon-based white empty cards with human copy | 75% | Illustration assets not reused due unclear rights | Polished reusable `EmptyView` and key copy | Optional custom owned illustrations later |
| Cards/buttons/chips | Soft white cards, clear CTAs, rounded chips | Stronger white cards, shadows, outlined/filled CTAs | 78% | Some secondary screens still dense | Theme and shared card polish | Continue after screenshots |
| Arabic copy | Old Arabic was simple but mixed with template wording | More natural and safer Arabic across key flows | 75% | Product-owner tone review still needed | Improved home, doctor, payment, empty, AI copy | PO/legal review |
| Website/landing parity | Old project had a public Doctor Finder website | New project has no equivalent public marketing website | 20% | Public landing, search hero, marketing sections | Gap documented only; no website built in Sprint 29 | Public launch blocker |
| Overall patient feeling | Old app felt polished and consumer-facing | New app is closer, especially Home + Doctors | 73% | Real imagery, website, richer doctor data, seeded screenshots | Safe visual polish applied | Not equal to old UI yet |

## Final Parity Estimate

Overall app visual parity after Sprint 29: **73%**.

This is enough to continue toward supervised pilot only if the pilot is app-focused, seeded, and reviewed with real screenshots. It is not equal to the old app/site and it is not ready to claim public-launch visual parity.
