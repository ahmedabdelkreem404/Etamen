# Sprint 27 Pilot Screenshot Checklist

Screenshots should be captured on the final pilot device/build after seed data exists.

## Captured During Sprint 27 Emulator Pass

- [x] Home after login: `I:/Etamen/.tmp/etamen-home-after-login.png`
- [x] Home after quick-card overflow fix: `I:/Etamen/.tmp/etamen-home-fixed2.png`
- [x] Services tab: `I:/Etamen/.tmp/etamen-services.png`
- [x] Doctors empty state: `I:/Etamen/.tmp/etamen-doctors.png`
- [x] Session restore: `I:/Etamen/.tmp/etamen-session-restore2.png`
- [x] Account page: `I:/Etamen/.tmp/etamen-account-s27.png`
- [x] Logout dialog XML: `I:/Etamen/.tmp/window-logout-dialog-s27.xml`
- [x] Login screen after logout XML: `I:/Etamen/.tmp/window-after-logout-s27.xml`
- [x] Home after Sprint 27 teal refresh reinstall: `I:/Etamen/.tmp/etamen-home-teal-s27.png`

## Still Required After Seed Data Exists

- [ ] Splash/login polished screenshot.
- [x] Home final teal palette after reinstall.
- [ ] Services final teal palette.
- [ ] Health hub.
- [ ] Doctors list with real doctor.
- [ ] Doctor profile.
- [ ] Booking slot selection.
- [ ] Payment manual method selection.
- [ ] Payment proof upload.
- [ ] Payment status verified/rejected.
- [ ] My appointments list/details.
- [ ] Pharmacy products.
- [ ] Pharmacy cart/order details.
- [ ] Labs tests/packages.
- [ ] Lab result card/download.
- [ ] Health dashboard.
- [ ] Add vital form.
- [ ] Medications today.
- [ ] Care plan details.
- [ ] Notifications list with unread item.
- [ ] AI chat refusal.
- [ ] AI red-flag banner.
- [ ] Account/legal/support pages.

## Capture Notes

- For emulator: use `adb -s emulator-5554 exec-out screencap -p`.
- For physical Android: use `adb devices` then capture from the device id.
- Keep screenshots out of commits unless intentionally added as QA artifacts.
