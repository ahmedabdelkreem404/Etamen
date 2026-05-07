# Sprint 31 Placeholder Data Report

Date: 2026-05-07

Sprint 31 used only safe visual placeholders. No fake real doctor photos, fake reviews, fake medical credentials, fake payment verification, or fake paid state were added.

## Placeholders Used

| Placeholder | Where | Why | Safety note |
| --- | --- | --- | --- |
| Doctor medical silhouette/avatar block | Home nearby doctors, Doctor cards, Doctor profile, Booking summary | Backend does not expose safe doctor image/avatar URL | Decorative only; initials and medical silhouette are not real person images |
| Rating visual row | Doctor list/profile | Old Doctor Finder card layout used star rows and the new app needed similar visual balance | Copy says approximate/patient rating; backend still needs real rating contract before production claims |
| Specialty/service icons | Home, Services, Doctor cards | Old app used icon-led cards | Uses Flutter icons only |
| Payment method icons | Payment method cards | Needed consumer-grade visual separation for Vodafone Cash and InstaPay | Does not imply Flutter verifies payment |
| Empty/upload illustration icons | Empty states and proof upload | Missing data should not look broken | Icons only, no private or third-party screenshots |

## Backend/Data Gaps

- `doctor.avatar_url` / `doctor.image_url` is missing from the displayed contract.
- Verified doctor rating and review count fields are missing.
- Public website doctor search content is not wired to a public marketing search endpoint.
- Real licensed app screenshots/phone mockups are not yet available for the website.

## Assets Reused

- No old Flutter app assets were copied.
- The old website hero image previously inspected and copied in Sprint 30 remains in backend public assets for the landing hero.
- No screenshots, private user photos, URLs with secrets, API keys, old auth assets, old payment assets, or unsafe old code were reused.
