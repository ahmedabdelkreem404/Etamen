# Sprint 30 Screenshot Inventory

Date: 2026-05-07

## Sources Checked

- `I:/Etamen/doctorfinder_timeslot-main`
- `I:/Etamen/etamen-backend/docs`
- `I:/Etamen/docs`
- `I:/Etamen/Website/PHPScript/storage/logs/doctor-finder-website-wait.png`

## Result

`I:/Etamen/etamen-backend/docs` contains no useful image files. The useful legacy image references were found in `I:/Etamen/docs`, the old Flutter project root, and the old website logs folder.

## Useful Legacy Images

| Path | Type | Screen represented | Useful for new UI | Notes |
| --- | --- | --- | --- | --- |
| `I:/Etamen/docs/68747470733a2f2f667265616b74656d706c6174652e636f6d2f7265736f75726365732f646f63746f7266696e6465722f70726573656e7433312e706e67.png` | app marketing | Mobile home, login by phone, doctor profile, book appointment | Yes | Strong teal/cyan identity, white rounded cards, doctor-first journey. |
| `I:/Etamen/docs/68747470733a2f2f667265616b74656d706c6174652e636f6d2f7265736f75726365732f646f63746f7266696e6465722f70726573656e7433322e706e67.png` | app marketing | Payment gateways, chat/video, profile, doctor dashboard | Yes | Useful for payment method cards and account/profile tone. Unsafe payment/chat logic was not copied. |
| `I:/Etamen/docs/68747470733a2f2f667265616b74656d706c6174652e636f6d2f7265736f75726365732f646f63746f7266696e6465722f70726573656e7433332e706e67.png` | app marketing | Reports, pharmacy, medicine orders | Partial | Useful for pharmacy/order card richness. |
| `I:/Etamen/docs/68747470733a2f2f667265616b74656d706c6174652e636f6d2f7265736f75726365732f646f63746f7266696e6465722f70726573656e7433342e706e67.png` | app/admin marketing | Labs, lab orders, reports, admin | Partial | Useful for labs tone, not for admin visuals. |
| `I:/Etamen/docs/68747470733a2f2f667265616b74656d706c6174652e636f6d2f7265736f75726365732f646f63746f7266696e6465722f74696d656c696e6531316d696e2e6a7067.jpg` | timeline | Old app timeline/screens | Yes | Confirms rounded teal cards and simple booking sequence. |
| `I:/Etamen/docs/68747470733a2f2f667265616b74656d706c6174652e636f6d2f7265736f75726365732f646f63746f7266696e6465722f657874656e646564312e706e67.png` | website/service | Service comparison/extended page | Partial | Useful for old marketing density and service grouping. |
| `I:/Etamen/docs/68747470733a2f2f667265616b74656d706c6174652e636f6d2f7265736f75726365732f646f63746f7266696e6465722f736572766963656c697374312e706e67.png` | website/service | Service list/pricing comparison | Partial | Useful for public website sections only. |
| `I:/Etamen/Website/PHPScript/storage/logs/doctor-finder-website-wait.png` | website | Old public landing page | Yes | Dark top strip, white nav, orange CTA, peach/teal split hero, medical image, rounded search. |
| `I:/Etamen/doctorfinder_timeslot-main/flutter_01.png` | old Flutter app | Recent chats empty state with teal header and bottom nav | Partial | Useful for shell/header/bottom-nav treatment. |

## Old App Assets Inspected

Useful visual-only assets were inspected under `I:/Etamen/doctorfinder_timeslot-main/assets`, especially:

- `homeScreenImages/header_bg.png`
- `detailScreenImages/*`
- `makeAppointmentScreenImages/day_active.png`
- `makeAppointmentScreenImages/time_active.png`

No unsafe old auth, networking, payment, FCM, chat/video, session, localhost, or API code was copied.

## Visual Lessons Applied

- Teal/cyan should dominate the first viewport.
- Doctor booking should be visually primary, not hidden inside a generic services grid.
- Doctor cards need image/avatar space, rating treatment, fee/location chips, and clear CTAs.
- Booking needs large day/slot tiles and obvious selection.
- Website landing needs peach/teal split, orange CTA, rounded search, and a real medical visual.

## Still Missing From Legacy References

- Real doctor images/avatars are not available from the current backend contract.
- Real ratings/review counts are not available from the current backend contract.
- Some old website sections were marketing/CMS content and were not rebuilt as a full CMS in Sprint 30.
