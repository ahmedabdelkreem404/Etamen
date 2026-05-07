# Sprint 31 Old UI Component Breakdown

Date: 2026-05-07

Sprint 31 used the old Doctor Finder app/site as strict visual reference only. No old auth, networking, payment, FCM, ConnectyCube, session, backend controller, localhost, hardcoded user/provider id, or secret logic was copied.

## References Inspected

- `I:/Etamen/docs/2dd1ca67b8d187837e9cabf96a56287d.png` (`present31.png`)
- `I:/Etamen/docs/4d4f24444fb307e514f79c908e8cf5e1.png` (`present32.png`)
- `I:/Etamen/docs/8c6edbac921bd547f029e3c903cbda1b.png` (`present33.png`)
- `I:/Etamen/docs/43a5b92e1d8c4175fbc68c47927ec1db.png` (`present34.png`)
- `I:/Etamen/docs/9457e97075e3603b6f442b6c7d8b2ef4.jpg` (`timeline11min.jpg`)
- `I:/Etamen/Website/PHPScript/storage/logs/doctor-finder-website-wait.png`
- `I:/Etamen/doctorfinder_timeslot-main/flutter_01.png`

## Old Mobile Home

Must be cloned:

- Bright cyan/teal top header with simple greeting.
- Large rounded white search field near the top.
- Square search/action button next to the search field.
- Doctor-finder promotional banner directly below header.
- White appointment card below banner.
- `Speciality` horizontal cards/pills.
- `Nearby Doctors` preview cards.
- Bottom navigation with simple icons and orange active state.

Can be modernized slightly:

- Arabic labels can remain more natural for Etamen.
- Doctor image can be a safe medical silhouette until backend exposes licensed images.

Cannot be fully cloned yet:

- Real old doctor/person imagery is not available as a safe reusable app asset.

## Old Doctor List

Must be cloned:

- Prominent top search bar.
- Specialty/category chips.
- Rounded white doctor cards.
- Doctor image/avatar area on card side.
- Doctor name as strongest typography.
- Specialty, fee, location, and rating grouped with chips/icons.
- Primary booking action; details action secondary.
- Teal surface accents and orange rating/selected accents.

Cannot be fully cloned yet:

- Backend contract still does not expose safe doctor `avatar_url` or `image_url`.
- Backend does not expose verified ratings/review counts.

## Old Doctor Profile

Must be cloned:

- Strong top doctor visual area.
- Large doctor image/avatar block.
- Clear name/specialty hierarchy.
- Rating row.
- Fee/location/experience grouped as rounded chips.
- About section in a white card.
- Available slots shown as simple day/time selection.
- Booking CTA visually prominent.

Can be modernized slightly:

- Safety wording can clarify that demo doctor data is not a real medical claim.

Cannot be fully cloned yet:

- Real doctor photo, review count, map/address, and verified credentials are missing.

## Old Booking

Must be cloned:

- Simple step flow.
- Date selector visually central.
- Time slot grid with obvious selected state.
- Orange selected date/slot state.
- Booking summary and confirmation CTA.

Sprint 31 clone decision:

- Replace the long all-days slot list with a horizontal day row plus current-day time tiles.
- Keep booking DTO/API untouched.

## Old Payment

Must be cloned:

- Consumer-grade method cards.
- Distinct manual payment options.
- Proof upload area as a polished card.
- Pending/rejected/success states in friendly language.

Cannot be fully cloned yet:

- Real payment brand artwork and gateway sheets were not reused because ownership/safety are unclear.
- Flutter must not verify payment or mark paid.

## Old Website Landing

Must be cloned:

- Dark top strip.
- White navigation.
- Orange `Join As Doctor` CTA.
- Peach/teal split hero.
- Big `Find A Doctor!` heading.
- Rounded search pill with orange action.
- Medical hero image on right.
- Service cards below first viewport.

Can be modernized slightly:

- Arabic-first Etamen pilot copy may sit around old English visual labels.

Cannot be fully cloned yet:

- Full old public website/CMS/search/doctor registration flow was not rebuilt in this visual sprint.
