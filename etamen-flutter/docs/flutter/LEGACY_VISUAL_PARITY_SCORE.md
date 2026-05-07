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

## Sprint 30 Hard Visual Correction

Sprint 30 reworked the app shell, Home, Doctor list, Doctor profile, Booking, Payment visuals, and added a lightweight public landing page. New screenshots were captured under `I:/Etamen/.tmp/sprint30-new-screenshots/`.

| Area | Old UI Reference Quality | New UI Current Quality | Parity % | Missing Pieces | Fix Applied In Sprint 30 | Remaining Gap |
| --- | --- | --- | ---: | --- | --- | --- |
| Color palette | Bright teal/cyan with peach/orange accents | Teal/cyan is now dominant across shell, cards, booking, payment, and website | 92% | Exact template shade balance | Updated theme, app bar, cards, chips, website hero | Minor tuning only |
| Typography | Bold marketing/app headings with simple readable labels | Stronger hierarchy, Arabic-first headings, cleaner labels | 84% | Exact old font family | Adjusted weights and sizing in core screens | Product-owner font choice |
| Home screen | Doctor-first polished hero/search | Strong teal hero, prominent search, doctor booking first | 91% | Real doctor/person imagery | Rebuilt first viewport and grouped actions | Needs final PO review |
| Bottom navigation / shell | Simple icon-led 5-tab shell | Custom old-style five-tab shell with active teal surface | 89% | Exact old icons/animation | Replaced generic NavigationBar treatment | Minor density tuning |
| Doctor cards | Doctor photo, clear specialty, fee, location, CTA | Premium cards with avatar placeholder, rating placeholder, chips, dual CTA | 90% | Real image URL and real ratings | Rebuilt `DoctorCard` and wrapped chips | Backend data gap |
| Doctor profile | Large doctor header and grouped booking info | Strong teal profile header, avatar, rating, fee/location/experience, about, slots | 88% | Real photo, real reviews/map | Header/slots/profile grouping polished | Backend data gap |
| Booking flow | Visual day/time selection and simple CTA | Large slot tiles, orange selected state, step indicator, simpler wording | 90% | Exact old slot artwork | Rebuilt slot picker and booking hierarchy | Date labels still partly English |
| Payment flow | Consumer-grade method cards/proof flow | Better method cards, manual proof upload, friendly status copy | 84% | Full old gateway artwork and real method branding | Reworked method/proof widgets and removed raw `pending_payment` | Needs real proof E2E review |
| Empty states | Friendly visual cards/illustrations | Friendly white-card empty states and less technical copy | 82% | Owned illustration pack | Copy and shared empty style improved | Optional custom illustrations |
| Cards/buttons/chips | Soft white cards, rounded chips, clear CTAs | Strong old-style white cards, soft shadows, wrapped specialty chips | 90% | Exact template microspacing | Theme and widget polish | Minor QA on small devices |
| Arabic copy | Simple patient-facing wording | More natural Arabic in Home, Doctor, Booking, Payment, AI/empty states | 86% | PO/legal tone pass | Humanized key copy and statuses | Product-owner review |
| Website/landing parity | Public Doctor Finder landing | Lightweight Laravel landing now matches first-viewport spirit | 84% | Full CMS/search/doctor join flow/SEO | Created Blade landing and visual report | Public-launch gap |
| Overall patient feeling | Polished consumer Doctor Finder feel | App now feels patient/booking-first rather than admin-first | 88% | Real data/images across all services | Hard visual correction applied | Not a 100% clone |

## Sprint 30 Final Parity Estimate

- App shell + doctor-booking flow: **90%**.
- Overall mobile app: **86%**.
- Public landing first viewport: **84%**.
- Overall old app/site parity: **88%**.

The new Etamen app is now close to the old Doctor Finder feeling in the doctor journey, but it is not an exact 1:1 restoration because real doctor imagery, ratings, and full old public website content are still missing.

## Sprint 31 Final Old UI/UX Clone Pass

Sprint 31 moved from "inspired by old UI" to a stricter visual clone pass. Screenshots were captured under `I:/Etamen/.tmp/sprint31-new-screenshots/`.

| Area | Old UI Reference Quality | New UI Current Quality | Parity % | Missing Pieces | Fix Applied In Sprint 31 | Remaining Gap |
| --- | --- | --- | ---: | --- | --- | --- |
| Color palette | Bright cyan/teal with orange action accents | Old primary cyan `#01D8C9`, white cards, orange selected/active states | 94% | Exact template color calibration | Tightened theme colors and nav/slot orange accents | Minor shade/contrast review |
| Typography | Bold simple Doctor Finder headings | Stronger old-like weights and smaller Home hero title | 88% | Exact old font family | Adjusted Home and card hierarchy | Product-owner font approval |
| Home screen | Teal header, search, promo banner, appointment card, specialties, nearby doctors | Matches old first-viewport structure closely | 94% | Real doctor/banner image and exact English-first layout | Rebuilt Home into old-style header/search/banner/appointment/speciality/nearby flow | Not 95% because imagery is placeholder |
| Bottom navigation / shell | White 5-tab shell with orange active icon | White five-tab shell with orange active state | 92% | Exact old icon set/animation | Removed generic selected Material surface | Microspacing differs |
| Doctor cards | Photo-based premium white doctor cards | Large safe avatar block, rating row, chips, fee/location/experience, old-style CTA area | 92% | Real doctor photo and verified rating fields | Rebuilt avatar/card density and placeholder treatment | Backend data gap prevents 95% |
| Doctor profile | Strong info card, photo, rating, grouped chips, about, slots | White old-style info card, avatar placeholder, rating row, chips, about, compact slots | 93% | Real photo, reviews, map/credential data | Reworked profile hero away from generic gradient and shortened slots | Backend data gap |
| Booking flow | MakeAppointment day/time selector and orange selected state | Stepper, doctor summary, horizontal day selector, orange selected day/time, confirmation card | 94% | Exact old date language/animation | Rebuilt SlotPicker to show day row + active-day slots only | CTA still below fold on some devices |
| Payment flow | Consumer method cards and proof flow | Polished manual method cards, proof upload card, friendly pending review copy | 90% | Full old gateway art/branding | Retained safe Sprint 30 payment polish and verified screenshots | Cannot copy old payment logic/assets |
| Empty states | Friendly visuals | Friendly white-card/icon empty states | 86% | Owned illustration set | Placeholder/icon style retained | Optional custom illustration pack |
| Cards/buttons/chips | Rounded white cards, teal chips, orange actions | Strong white cards, soft borders, teal chips, orange active states | 93% | Exact pixel spacing | Theme/card/chip/slot updates | Small-device polish remains |
| Arabic copy | Simple patient copy mixed with old English template | Arabic-first patient copy, with old English visual labels where helpful | 88% | Product-owner tone approval | Confirmed no raw technical payment status in visible flow | PO/legal review still needed |
| Website/landing parity | Old Doctor Finder public landing first viewport | Dark strip, white nav, orange CTA, peach/teal hero, `Find A Doctor!`, search pill, service cards | 90% | Full old marketing site/CMS/search flow | Tightened `welcome.blade.php` to old first viewport | Public launch content gap |
| Overall patient feeling | Polished consumer Doctor Finder product | Doctor-booking path feels close to old app/site, not admin-like | 91% | Real photos/ratings/full website | Final clone pass applied safely | Still not exact old app |

## Sprint 31 Final Parity Estimate

- Home: **94%**.
- Doctor list: **92%**.
- Doctor profile: **93%**.
- Booking flow: **94%**.
- Payment visual flow: **90%**.
- Website landing first viewport: **90%**.
- Overall mobile app: **91%**.
- Overall app/site: **91%**.

The requested 95%+ doctor journey target was not honestly reached because real doctor photos, real ratings/reviews, exact old assets, and some old copy/layout details are missing. The new visual layer is now close enough for product-owner screenshot review, but it should not be described as "the same as the old app".
