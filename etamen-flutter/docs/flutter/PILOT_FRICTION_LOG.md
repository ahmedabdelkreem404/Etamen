# Sprint 27 Pilot Friction Log

| Flow | Issue | Severity | Fix Applied | Remaining Action | Pilot Impact |
| --- | --- | --- | --- | --- | --- |
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
