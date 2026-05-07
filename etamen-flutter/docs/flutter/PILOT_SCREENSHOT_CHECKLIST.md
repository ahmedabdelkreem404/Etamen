# Sprint 27 Pilot Screenshot Checklist

Screenshots should be captured on the final pilot device/build after seed data exists.

## Captured During Sprint 28 Seeded Emulator Pass

Screenshots were saved under `I:/Etamen/.tmp/pilot-screenshots/`.

- [x] Login screen: `I:/Etamen/.tmp/pilot-screenshots/01-login.png`
- [x] Home after first successful login: `I:/Etamen/.tmp/pilot-screenshots/02-home.png`
- [x] Services tab: `I:/Etamen/.tmp/pilot-screenshots/03-services.png`
- [x] Doctors list with demo doctor: `I:/Etamen/.tmp/pilot-screenshots/04-doctors.png`
- [x] Doctor profile before slots fix, showing error state: `I:/Etamen/.tmp/pilot-screenshots/05-doctor-profile.png`
- [x] Booking page before slots fix, showing error state: `I:/Etamen/.tmp/pilot-screenshots/06-booking.png`
- [x] Booking retry before slots fix: `I:/Etamen/.tmp/pilot-screenshots/07-booking-after-tap.png`
- [x] App route after slots fix/reinstall: `I:/Etamen/.tmp/pilot-screenshots/08-after-fix-route.png`
- [x] Home/login state after slots fix: `I:/Etamen/.tmp/pilot-screenshots/09-after-fix-home.png`
- [x] Invalid/stale login state after database reset: `I:/Etamen/.tmp/pilot-screenshots/10-after-fix-login2.png`
- [x] Stale protected payment route after database reset: `I:/Etamen/.tmp/pilot-screenshots/11-home-after-db-fix.png`
- [x] Clean app login attempts after data clear: `I:/Etamen/.tmp/pilot-screenshots/12-clean-home-after-fix.png` through `17-final-login-try.png`
- [x] Clean login screen after final Sprint 28 seed/reset: `I:/Etamen/.tmp/pilot-screenshots/18-clean-login-after-final-seed.png`

Notes:

- The screenshots prove launch, login/home, services, seeded doctor visibility, and the doctor slots mismatch that was fixed.
- Full payment/pharmacy/labs/health/AI screenshots remain pending because post-reset automated ADB text entry was unreliable. Manual entry on emulator or a real Android device is the next required pass.

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

- [x] Splash/login polished screenshot.
- [x] Home final teal palette after reinstall.
- [x] Services final teal palette.
- [ ] Health hub.
- [x] Doctors list with real doctor.
- [x] Doctor profile error evidence before slots fix.
- [ ] Booking slot selection after slots fix.
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
