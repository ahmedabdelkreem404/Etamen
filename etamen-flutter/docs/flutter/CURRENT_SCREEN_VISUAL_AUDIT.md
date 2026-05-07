# Sprint 29 Current Screen Visual Audit

Date: 2026-05-07

This audit was performed by inspecting the current Flutter implementation after Sprint 29 polish. Live screenshot capture is still pending a seeded/runnable authenticated pilot environment, so scores are code-and-layout based.

| Screen | Current Visual Quality /10 | Old UI Parity /10 | What Feels Good | Still Weak | Improve Before Pilot |
| --- | ---: | ---: | --- | --- | --- |
| Home | 8.0 | 7.5 | Strong teal hero, greeting, search, doctor booking highlight, quick actions, notification badge, calmer AI CTA. | No real old doctor image/banner asset; current layout is modernized rather than identical. | Capture on small devices and verify no overflow with Arabic names. |
| Services | 7.5 | 7.0 | Clear grouped doctors/pharmacy/labs cards with white surfaces and CTAs. | Still more operational than the old marketing-like service panels. | Validate with real service data and icons. |
| Health hub | 7.0 | 6.0 | Calm cards, safe medical copy, good grouping. | Health hub is a new product area, not a direct old Doctor Finder parity screen. | Keep it visually secondary to doctor booking in pilot. |
| Doctors list | 8.0 | 7.5 | New premium doctor card, initials avatar, specialty/location/fee chips, primary/secondary CTAs. | Real doctor images are missing from the contract; ratings/availability density are weaker than old app. | Backend should add doctor avatar/image URL and real next-available slot summary. |
| Doctor profile | 8.0 | 7.4 | Strong teal profile header, avatar placeholder, grouped fee/location/experience, about card, slot card. | No doctor photo, rating/reviews, map/address details, or rich media. | Add real avatar URL and confirm profile layout on narrow devices. |
| Booking | 7.6 | 7.0 | Step indicator, doctor summary, grouped day/time slots, clear confirm CTA. | Less polished than old bottom booking strip; slot availability depends on data. | Test full flow with seeded available slots. |
| Payment manual | 7.2 | 6.5 | Safer review wording, summary card, manual proof flow remains clear. | Old app showed payment method sheets with more visual richness. | Verify proof upload UX on device and improve method cards if real methods are dense. |
| My appointments | 7.0 | 6.5 | Uses patient-facing empty copy and status flow. | Old upcoming appointment card on home was more prominent. | Seed demo appointments and polish status chips after E2E. |
| Pharmacy products | 7.0 | 6.0 | Existing card/service direction is acceptable and empty copy improved. | Old pharmacy screens had product imagery and richer product cards. | Product image URLs and seed data are needed for fair visual parity. |
| Labs | 7.0 | 6.0 | Clean lab/test grouping and friendly empty copy. | Old lab flow had more visual report/package context. | Seed lab/test/package data and screenshot flow. |
| Health dashboard | 7.0 | 5.5 | Readable and safe. | Not an old Doctor Finder core screen, so parity is naturally limited. | Keep medical disclaimers concise and non-alarming. |
| AI | 7.0 | 5.5 | Safer disclaimer and friendlier empty conversation copy. | Old timeline references AI, but no comparable production-quality old screen was available. | Product-owner review of AI wording is still needed. |
| Account | 7.0 | 6.5 | Clean settings/legal/support structure. | Old app profile screens used stronger profile visuals. | Add real profile completion/photo only if backend contract supports it safely. |

## Cross-Screen Findings

- The new app now reads more like a patient app than an admin shell, especially on Home and Doctors.
- Color, cards, chips, and empty states moved closer to the old teal/cyan Doctor Finder direction.
- Real visual parity is limited by data contracts: doctor image/avatar URL, next available slot summary, ratings/reviews, and richer product/lab images are absent.
- No full public website/landing parity exists in the new project.
