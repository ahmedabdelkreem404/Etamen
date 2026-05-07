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

## Severity Notes

- Blocker: user cannot complete a core pilot flow.
- High: major feature flow cannot be validated.
- Medium: visible UX issue or missing seeded path.
- Low: polish/environment/documentation issue.
