# Sprint 27 Pilot Friction Log

| Flow | Issue | Severity | Fix Applied | Remaining Action | Pilot Impact |
| --- | --- | --- | --- | --- | --- |
| Sprint 28 seed data | Full pilot walkthrough was blocked because core demo data did not exist. | Blocker | Added `PilotDemoSeeder` with demo patient, providers, doctor slots, manual payment methods, pharmacy products, labs, vitals, medication reminder, care plan, notification, and demo lab result. | Keep this seeder local/staging only and never auto-run in production. | Unblocks realistic local/staging walkthrough setup. |
| Backend migration | MySQL rejected long generated index names during `migrate:fresh`. | Blocker | Added explicit short names for affected appointments, pharmacy, medications, and care plan indexes. | Re-run migrations in CI/staging database. | Allows clean local/staging reset and seeding. |
| Backend migration | MySQL rejected non-null appointment timestamp columns with no default. | Blocker | Changed doctor holiday and appointment slot start/end columns to `dateTime`. | Keep API serialization unchanged. | Allows local/staging reset on MySQL. |
| Backend seeding | Default AI provider config seeder stored encrypted data in a JSON column, failing on MySQL JSON constraints. | Blocker | Changed `ai_provider_configs.encrypted_config` to text, matching Laravel encrypted cast output. | No AI secrets were added. | Allows baseline seed and PilotDemoSeeder to run. |
| Doctor slots | Flutter sent `per_page` to doctor slots, while backend endpoint accepts `limit`; slots looked like a network error in doctor profile/booking. | High | Updated Flutter doctor remote data source to send `limit`. | Retest doctor profile and booking with seeded doctor after clean app login. | Removes a booking blocker discovered during emulator walkthrough. |
| Auth after DB reset | Existing Flutter token became invalid after `migrate:fresh`, and the app showed an unauthenticated state on a stale protected route. | Medium | Documented the need to clear app data/reinstall after database reset. | Improve auth error Arabic copy in a later hardening pass if still visible. | Avoids confusing QA after backend reset. |
| Emulator automation | ADB text entry intermittently truncated the `.test` email after app data clear, preventing a fully automated post-seed login repeat. | Low | Verified credentials through host API and documented the automation limitation. | Manually type credentials on emulator/real device for final walkthrough. | Does not block app logic, but blocks unattended walkthrough automation. |
| Payment/admin review | Payment proof upload and admin acceptance were not completed after seed setup. | Blocker | Seeded payment methods and doctor data needed to create a pending payment. | Complete manual payment proof upload and admin accept/reject pass. | Paid appointment confirmation remains unproven end-to-end. |
| Pharmacy/labs orders | Products/tests now exist, but order creation/payment was not completed in the final automated pass. | High | Seeded pharmacy, products, lab, tests, package, and demo lab result. | Complete order creation, payment, and result download manually. | Commerce/lab pilot flow still needs user-level validation. |
| Home | Quick action cards overflowed on emulator after Sprint 26 refresh. | Medium | Reduced quick-card padding/density and adjusted grid aspect ratio. | Recheck on physical device sizes. | Prevents visible layout break on first screen. |
| Visual design | New app still felt farther from old Doctor Finder teal/cyan brand feel. | Low | Restored teal-inspired primary palette, old accent color, subtle card shadow, teal app bars. | Product/design review after screenshots. | Improves trust and familiarity for pilot. |
| Legacy app run | Old app could not run on current Flutter due outdated Gradle plugin style. | Medium | Treated old app as visual reference only. No unsafe code copied. | Migrate old app only if historical demo is required. | Does not block new app pilot, but prevents direct side-by-side runtime comparison. |
| Doctors | Doctors list reached but empty. | Blocker | No frontend fix; data issue documented. | Seed approved doctor, specialty, branch, slots, fee. | Booking cannot be tested or piloted. |
| Payments | Payment methods endpoint returned empty. | Blocker | No frontend fix; data issue documented. | Seed active manual Vodafone Cash/InstaPay methods. | Paid appointment flow cannot be tested. |
| Pharmacy | Pharmacies endpoint returned empty. | High | No frontend fix; data issue documented. | Seed approved pharmacy and products. | Pharmacy pilot flow cannot be tested. |
| Labs | Labs endpoint returned empty. | High | No frontend fix; data issue documented. | Seed approved lab/tests/results. | Lab pilot flow cannot be tested. |
| Care Plans | No active plan for test patient. | Medium | No frontend fix; data issue documented. | Assign/create active care plan. | Care plan tracking cannot be tested. |
| Notifications | Notifications empty. | Low | No frontend fix; data issue documented. | Seed or trigger a notification. | Badge/read flows remain unproven with real data. |
| Auth | Login/session restore/logout worked. | None | Verified on emulator. | Retest on physical Android LAN/staging. | Core access flow is ready for next QA pass. |
| Emulator environment | Emulator storage had been full during earlier app run/build attempts. | Medium | Freed emulator space by uninstalling unrelated test apps during prior setup. | Monitor emulator/device free space before QA. | Environment issue, not app logic. |
| Old app cache | Temporary `.gradle-old` cache was created while trying to run old app. | Low | Hidden locally from Git; cleanup attempted but some files were held by Java/Gradle process. | Delete after Java/Gradle releases file locks. | Does not affect app runtime. |
| Sprint 32 doctor visuals | Old Doctor Finder parity was blocked by missing doctor avatar/rating fields. | High | Added safe nullable `avatar_path`, public `avatar_url`, approved visible rating summary, and richer demo doctor data. | Replace demo placeholders with product-owner-approved public doctor images before production/public launch. | Raises doctor journey visual trust for supervised pilot. |
| Sprint 32 payment proof | Proof upload screen was reached, but native file picker upload was not completed in automated emulator pass. | Blocker | Payment proof screen copy and layout are polished; no raw backend status shown. | Perform one physical-device proof image upload and admin review before inviting external pilot users. | Keeps pilot gate at "ready after minor verification" instead of fully ready. |
| Sprint 32 admin payment review | Admin review path was not exercised during Flutter walkthrough. | Blocker | No unsafe Flutter-side verification added. | Run Filament/admin payment accept/reject walkthrough with seeded appointment. | Needed for paid booking operational readiness. |
| Sprint 32 website image | Public hero asset URL returned the landing HTML from the local Laravel server, so the hero image did not render in screenshots. | Medium | Embedded the small approved hero image as a Data URI for the lightweight landing screenshot. | For public launch, serve the image as a normal static asset/CDN URL and remove Data URI if performance requires. | Website first viewport now visually reviewable. |
| Sprint 32 emulator environment | Android System UI showed one ANR dialog before login. | Low | Dismissed with `Wait`; Etamen app stayed responsive and E2E continued. | Repeat on physical device to avoid emulator-only noise. | Environment issue, not app logic. |
| Sprint 32 build environment | ARM64 APK build initially failed because `GRADLE_USER_HOME` pointed to full `D:/gradle_home`. | Medium | Reran the build with the session Gradle cache on the user profile disk; ARM64 build passed. | Free or relocate `D:/gradle_home` before future local builds. | Environment/storage issue, not app logic. |

## Severity Notes

- Blocker: user cannot complete a core pilot flow.
- High: major feature flow cannot be validated.
- Medium: visible UX issue or missing seeded path.
- Low: polish/environment/documentation issue.

---

# Sprint 33 Final Physical Device Pilot Verification

Sprint 33 was attempted on 2026-05-08, but no physical Android device was detected by ADB. The only attached device was `emulator-5554`, so no physical-device screenshots, real proof upload, or admin review of a phone-created proof could be completed.

| ID | Flow | Severity | Description | Screenshot / Evidence | Fix Applied | Retest Result | Remaining Owner |
| --- | --- | --- | --- | --- | --- | --- | --- |
| S33-001 | Device setup | BLOCKER | No physical Android device was detected; ADB listed emulator only. | `adb devices -l` output in `SPRINT33_PHYSICAL_DEVICE_ENVIRONMENT.md`. | None; environment/hardware required. | NOT RETESTED. | QA / device owner. |
| S33-002 | Backend reachability from phone | BLOCKER | Backend LAN URL could not be verified from phone because no phone was connected. | Intended URL: `http://192.168.1.5:8000/api/v1`. | Backend seeded and tests passed. | NOT TESTED. | QA / device owner. |
| S33-003 | Physical login/session | BLOCKER | Login/session restore/logout were not tested on physical Android. | No Sprint 33 physical screenshots captured. | APK built for ARM64. | NOT TESTED. | QA. |
| S33-004 | Doctor booking on physical device | BLOCKER | Booking path was not executed on physical Android. | No Sprint 33 physical screenshots captured. | Sprint 32 emulator path remains valid but insufficient for this gate. | NOT TESTED. | QA. |
| S33-005 | Real proof upload | BLOCKER | No real gallery/camera proof image was uploaded from a physical phone. | No `09-image-picker-selected.png` or `10-proof-uploaded-pending-review.png`. | No code change; cannot fake this. | NOT TESTED. | QA. |
| S33-006 | Admin review of same payment | BLOCKER | Admin accept/reject was not executed against a phone-created proof. | `SPRINT33_ADMIN_PAYMENT_REVIEW.md`. | Backend automated payment tests passed. | NOT TESTED. | Admin QA. |
| S33-007 | Flutter status after admin review | BLOCKER | Flutter could not be refreshed after admin accept because no phone-created payment was reviewed. | No `15-flutter-payment-verified.png` or `16-flutter-appointment-confirmed.png`. | None. | NOT TESTED. | QA / Admin QA. |
| S33-008 | Pharmacy/lab physical scope | MEDIUM | Pharmacy/lab basics were not tested on physical Android. | `SPRINT33_PILOT_SCOPE_DECISION.md`. | Scoped out unless a later physical pass proves them. | NOT TESTED. | Product / QA. |
| S33-009 | Local build storage | LOW | ARM64 build initially hit low disk space on drive `I:` while writing Flutter build artifacts. | Build logs; final build path `build/app/outputs/flutter-apk/app-debug.apk`. | Deleted generated `build` and `.dart_tool/flutter_build` safely, then rebuilt. | PASS. | Dev environment. |

Sprint 33 blocker decision: unresolved BLOCKER items remain, so the first 5-20 supervised pilot users must **not** be invited from this run.

---

# Sprint 34 Final UI/UX Finish + Emulator QA

| ID | Flow | Severity | Description | Screenshot / Evidence | Fix Applied | Retest Result | Remaining Owner |
| --- | --- | --- | --- | --- | --- | --- | --- |
| S34-001 | Brand colors | MEDIUM | Yellow/orange was still being used as a brand-like accent in booking, payment, rating, and landing CTAs. | `SPRINT34_COLOR_AUDIT.md`. | Replaced with teal/dark-teal/aqua tokens and removed `appointmentOrange`. | PASS on source audit and screenshots. | Product/design review. |
| S34-002 | Website mobile layout | MEDIUM | Initial headless mobile screenshots showed horizontal clipping in the landing first viewport. | Final `12-website-ar-mobile.png` and `14-website-en-mobile.png`. | Constrained mobile hero/card/search widths and fixed search-button flex behavior. | PASS in final mobile screenshots. | None for Sprint 34. |
| S34-003 | Arabic default | LOW | Needed explicit Sprint 34 confirmation that Arabic remained the default. | `00-login-ar.png`, `_reopen-after-logout.png`, `SPRINT34_AR_EN_LOCALIZATION_REVIEW.md`. | Added default-locale test and reviewed emulator screens. | PASS. | Product/legal for final wording. |
| S34-004 | Emulator logout | LOW | Logout/session restore needed an emulator smoke pass after UI changes. | `_after-logout-tap.png`, `_login-after-logout.png`, `_reopen-after-logout.png`. | No code fix required. | PASS on emulator. | Physical-device QA still pending. |
| S34-005 | Physical proof upload | BLOCKER | Sprint 34 did not and could not prove real phone gallery/camera proof upload. | Sprint 33 blocker remains. | None; no fake upload added. | NOT TESTED. | QA with physical Android device. |
| S34-006 | Admin review of phone-created proof | BLOCKER | Admin accept/reject still depends on a real proof uploaded from a phone. | Sprint 33 blocker remains. | None; Flutter still cannot verify payments. | NOT TESTED. | Admin QA / QA. |

Sprint 34 decision: UI polish is accepted for emulator/product visual review, but unresolved Sprint 33 physical-device BLOCKERS still prevent inviting pilot users.
