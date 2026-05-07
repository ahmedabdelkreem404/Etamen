# Sprint 29 Old UI Image Audit

Date: 2026-05-07

## Scope

Primary requested source `I:/Etamen/etamen-backend/docs` was inspected recursively for `.png`, `.jpg`, `.jpeg`, and `.webp` files. No image files were found there.

Additional legacy UI references were found outside that folder and inspected because they clearly belong to the old Doctor Finder product:

- `I:/Etamen/docs`
- `I:/Etamen/Website/PHPScript/storage/logs`
- selected visual assets from `I:/Etamen/doctorfinder_timeslot-main/assets`

No unsafe old auth, networking, payment, FCM, chat/video, localhost, session, API, or secret code was copied.

## Useful Legacy Screenshots

| File | Type | Main Colors | Layout / Components | What Is Good | Inspiration To Copy | What Not To Copy |
| --- | --- | --- | --- | --- | --- | --- |
| `I:/Etamen/docs/2dd1ca67b8d187837e9cabf96a56287d.png` (`present31.png`) | Mobile app marketing sheet | Teal/cyan, white cards, small orange accents | Login/register, home, doctor list, doctor profile, booking slots | Strongest old app reference. Home feels patient-first, with a prominent cyan header, search, doctor banner, appointment card, specialty chips, and doctor image cards. | Teal hero, white cards, doctor-first quick action, premium doctor cards, clear booking CTA, slot grouping. | Do not copy source code, auth flow, API assumptions, old payment/session behavior, or user/private doctor photos. |
| `I:/Etamen/docs/4d4f24444fb307e514f79c908e8cf5e1.png` (`present32.png`) | Mobile app feature sheet | Teal/cyan, white cards, orange action chips | Payment gateways, profile, doctor subscription, chat/video, prescription | Shows old product breadth and polished card rhythm. | Payment safety banner, profile grouping, calm cards. | Do not copy payment gateway keys, ConnectyCube/chat/video logic, FCM, subscription logic, or payment behavior. |
| `I:/Etamen/docs/8c6edbac921bd547f029e3c903cbda1b.png` (`present33.png`) | Mobile app feature sheet | Teal/cyan, white cards, product imagery | Lab reports, pharmacy list/products, checkout, payment method sheet | Pharmacy/lab flows look like consumer app screens, not admin pages. | Product/lab card spacing, bottom CTA clarity, friendly payment sheet wording. | Do not copy product images, hardcoded payment methods, or checkout implementation. |
| `I:/Etamen/docs/43a5b92e1d8c4175fbc68c47927ec1db.png` (`present34.png`) | Mobile app + admin sheet | Teal app bars, white content, admin blue/gray | Lab screens and admin panels | Confirms teal mobile identity; admin screens should not drive patient UI. | Use teal mobile app bars/cards only. | Do not make the patient Flutter app feel like the admin panel. |
| `I:/Etamen/docs/9457e97075e3603b6f442b6c7d8b2ef4.jpg` (`timeline11min.jpg`) | Timeline / product history | Teal/cyan and white, multiple app/site panels | AI chat, pharmacy, Stripe, prescription, video/chat, website version, home screenshot | Shows the old app evolved as a polished template with website + app parity. | Retain doctor finder identity and note website gap. | Do not copy third-party feature logic, gateway logic, chat/video code, or timeline artwork into the app. |
| `I:/Etamen/docs/81d2c3f5253b32eedec483d7cb535c2a.png` (`extended1.png`) | Template/service sheet | Cyan, white, gray | Service/license blocks | Useful only as evidence of template color/card style. | Teal/cyan cards and soft presentation. | Do not copy licensing/marketing imagery. |
| `I:/Etamen/docs/85041b47b34666442bef207a6d7b56c2.png` (`servicelist1.png`) | Template/service sheet | Cyan, white, gray | Feature/service listing | Useful for clean spacing and icon blocks. | Service-card rhythm. | Do not copy template text/assets. |
| `I:/Etamen/Website/PHPScript/storage/logs/doctor-finder-website-wait.png` | Website landing | White, teal, beige, orange CTA | Top contact bar, nav, "Find A Doctor!" hero, search card, doctor imagery | Old web landing is clearly public marketing and feels polished. | Public marketing website direction if a future sprint builds it. | Do not copy website code/assets blindly; rights and old implementation are unclear. |
| `I:/Etamen/Website/PHPScript/storage/logs/doctor-finder-website.png` | Website loader | Teal/white | Loading spinner/page | Confirms old website existed, but not useful for UX polish. | None. | Do not treat as website parity evidence beyond existence. |
| `I:/Etamen/Website/PHPScript/storage/logs/doctor-finder-admin.png` | Admin login | Gray/white/admin blue | Admin login panel | Confirms old admin was separate from patient UI. | None for patient app. | Do not make patient UI admin-like. |

## Old App Visual Assets Inspected

| File | Safe To Reuse? | Notes |
| --- | --- | --- |
| `doctorfinder_timeslot-main/assets/homeScreenImages/header_bg.png` | No reuse needed | Plain cyan rounded header background. Recreated equivalent in Flutter theme/hero instead of copying. |
| `doctorfinder_timeslot-main/assets/homeScreenImages/doctor.PNG` | No | Doctor/person photo rights are unclear. Do not reuse. |
| `doctorfinder_timeslot-main/assets/homeScreenImages/no_appo_img.png` | No | Empty-state illustration rights are unclear. Recreated empty state with icons instead. |
| `doctorfinder_timeslot-main/assets/makeAppointmentScreenImages/day_active.png` | No | Used only as inspiration for warm slot/day accent. |
| `doctorfinder_timeslot-main/flutter_01.png` | No | Screenshot-like asset. Do not ship inside new app. |

## Key Visual Lessons

- Old Doctor Finder felt like a patient consumer app because the first screen was teal/cyan, friendly, and doctor-first.
- White cards, soft shadows, rounded modules, visible doctor imagery, and direct booking CTAs carried most of the perceived polish.
- The old app did not look like an admin system; admin screens were visually separate.
- Doctor images were a major part of the old card quality. The current backend contract does not expose a doctor image/avatar URL, so Sprint 29 used polished initials placeholders and documents that backend gap.
- Website parity is not restored because the new project currently has no matching public marketing website.
