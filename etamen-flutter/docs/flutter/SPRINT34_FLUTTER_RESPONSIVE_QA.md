# Sprint 34 Flutter Responsive QA

Date: 2026-05-08

## Device / Emulator

- Device: Android emulator `sdk_gphone64_x86_64`
- Resolution: `1344x2992`
- Density: `480`
- Package: `com.etamen.etamen_app`
- APK installed: `I:/Etamen/etamen-flutter/build/app/outputs/flutter-apk/app-debug.apk`
- API base used for emulator QA: `http://10.0.2.2:8000/api/v1`
- Backend seeded before walkthrough: yes, `migrate:fresh --seed` plus `PilotDemoSeeder`

## Screen Results

| Screen | Result | Notes |
| --- | --- | --- |
| Login | PASS | Arabic-first login shown after logout/reopen. |
| Home | PASS | Teal header, search, doctor CTA, no yellow/orange accent. |
| Doctors list | PASS | Cards, avatar/rating, CTA render without orange/yellow accents. |
| Doctor profile | PASS | Header, slots, fee/rating, selected state render in teal. |
| Booking | PASS | Sticky CTA visible after slot selection; teal selected slot. |
| Payment methods | PASS | Manual payment cards are teal/blue-teal. |
| Proof upload screen | PASS visual only | Screen reached; real physical proof upload remains Sprint 33 blocker. |
| My appointments | PASS | No raw backend status observed in captured view. |
| Pharmacy | PASS visual/list | Pharmacy list rendered with seeded data; full order creation was not part of Sprint 34. |
| Labs | PASS visual/list | Lab list rendered with seeded data; full order creation was not part of Sprint 34. |
| Health | PASS | Dashboard cards visible; AI/medications/care-plan entry points visible. |
| Account/legal/support | PASS | Account, language, legal/support entries readable; logout works on emulator. |
| Bottom navigation | PASS | Five tabs only; active state teal. |

## Responsive Notes

- Large emulator viewport had no visible Flutter overflow in required screens.
- Small physical-device responsive behavior is still not approved because Sprint 33 had no real Android phone connected.
- Booking CTA remains visible on emulator with selected slot summary.

## Remaining QA

- Repeat the same visual pass on a small physical Android device.
- Capture English app screenshots after switching language from Account.
- Complete real gallery/camera payment proof upload on a phone.

