# Sprint 26 Legacy Asset Reuse Report

## Asset Reuse Decision

No legacy assets were copied into the new clean Flutter app during Sprint 26.

## Why

The old app contains useful visual references, but it also includes many legacy integration areas:

- ConnectyCube/video call assets and code.
- Firebase/notification related code.
- Old payment gateway imagery.
- Old upload receipt examples.
- Old app branding that may not match Etamen's current identity.

To avoid accidental unsafe migration, Sprint 26 reused only UX patterns:

- Green header feel.
- White card surfaces.
- Rounded avatar/image blocks.
- Search and quick action placement.
- Clear doctor card CTA.

## Asset Safety Result

No new asset privacy or licensing risk was introduced.

## Future Recommendation

Create fresh Etamen-branded assets:

- App launcher icon.
- Doctor/avatar placeholders.
- Home hero illustration or photo set.
- Pharmacy/lab/health module icons.

These should be designed or sourced specifically for Etamen rather than copied from the old project.

## Sprint 29 Update

No legacy assets were reused in Sprint 29.

### Assets Inspected

- `doctorfinder_timeslot-main/assets/homeScreenImages/header_bg.png`
- `doctorfinder_timeslot-main/assets/homeScreenImages/doctor.PNG`
- `doctorfinder_timeslot-main/assets/homeScreenImages/no_appo_img.png`
- `doctorfinder_timeslot-main/assets/makeAppointmentScreenImages/day_active.png`
- old screenshots in `I:/Etamen/docs`
- old website screenshots in `I:/Etamen/Website/PHPScript/storage/logs`

### Decision

The old assets were used as visual inspiration only. Sprint 29 recreated the teal hero, cards, empty states, and avatar placeholders with Flutter widgets and theme colors.

### Why No Assets Were Copied

- Old doctor/person photos may have unclear rights.
- Screenshots should not be shipped as app assets.
- Template/license images should not be embedded into the new app.
- Some old project areas include unsafe integrations or secrets nearby, so direct asset migration would increase review risk.

### Backend/Product Gap

The current doctor contract does not expose a safe `avatar_url` or `image_url`. Sprint 29 uses a polished initials placeholder instead. Real doctor images should be added only after the backend contract and asset rights are clear.
