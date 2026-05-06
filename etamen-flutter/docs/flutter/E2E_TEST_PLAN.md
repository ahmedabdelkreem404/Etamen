# Etamen Pilot E2E Test Plan

Use this checklist on a real Android device before giving the app to pilot users. Each case should be tested against staging/pilot data with Arabic UI first.

## Test Case Template

For each row:

- Preconditions: user/account/data required.
- Steps: execute the flow on device.
- Expected result: user-friendly Arabic state, no crash, no raw technical error.
- Result: `[ ] Pass` `[ ] Fail`
- Notes: record backend response, screenshot, or issue link.

## Auth

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Register | Backend running; unique email/phone | Open Register, submit valid fields | Account created or clear backend validation shown | [ ] | |
| Login | Patient exists | Login with valid credentials | Token saved, Home opens | [ ] | |
| Session restore | Logged in once | Kill app and reopen | Splash validates `/me`, Home opens | [ ] | |
| Logout | Logged in | Account > Logout > confirm | Backend logout attempted, token cleared, Login opens | [ ] | |
| Expired token | Expire/revoke token | Open protected page | "انتهت الجلسة، سجل دخول مرة أخرى" and Login opens | [ ] | |

## Doctors / Appointments

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Browse doctors | Approved doctor seeded | Open Doctors | List/cards or helpful empty state | [ ] | |
| Doctor profile | Doctor exists | Tap doctor | Details, fee, specialty, branch, slots section | [ ] | |
| Slots | Doctor has slots | Open profile/slots | Available slots grouped safely | [ ] | |
| Book appointment | Slot available | Select slot, type, description, confirm | Confirmed or pending payment state | [ ] | |
| Pending payment | Paid booking | Complete booking | Payment CTA appears with payment id | [ ] | |
| My appointments | Appointment exists | Open My Appointments | Appointment card appears | [ ] | |
| Appointment details | Appointment exists | Tap card | Details and payment card load | [ ] | |
| Cancel allowed appointment | Cancellable state | Tap cancel and confirm | Backend message, details refresh | [ ] | |

## Payments

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Payment methods | Payment id exists | Open payment page | Active methods load | [ ] | |
| Vodafone Cash | Manual method active | Select Vodafone Cash | Backend instructions shown | [ ] | |
| InstaPay | Manual method active | Select InstaPay | Backend instructions shown | [ ] | |
| Proof upload | Local image available | Upload screenshot | Pending review state | [ ] | |
| Pending review | Proof uploaded | Open status | Polling/refresh shows pending review | [ ] | |
| Admin accepts | Admin verifies payment | Refresh/poll status | Verified state and source details CTA | [ ] | |
| Rejected retry | Admin rejects proof | Open status | Rejection reason and retry CTA | [ ] | |
| Paymob foundation | Paymob configured | Create session/open checkout | External checkout opens if URL returned; backend status remains source of truth | [ ] | |
| Polling | Pending status | Wait 5-10 seconds | Polling stops on terminal status and respects 429 | [ ] | |

## Pharmacy

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Browse pharmacies | Active pharmacy seeded | Open Pharmacies | List or empty state | [ ] | |
| Browse products | Products seeded | Open pharmacy products | Product cards load | [ ] | |
| Cart | Products available | Add/update/remove product | Local cart updates; totals marked approximate | [ ] | |
| Prescription upload | Required product | Upload image | Prescription id returned and no raw path shown | [ ] | |
| Create order | Cart valid | Submit order | Cart clears after success, details open | [ ] | |
| Review state | Backend requires review | Open details | Pharmacy review state shown | [ ] | |
| Pay order | Order payable | Tap payment CTA | Existing payment UI opens with pharmacy context | [ ] | |
| Order details/status | Order exists | Open My Pharmacy Orders > details | Status/payment/items visible | [ ] | |

## Labs

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Browse labs | Active lab seeded | Open Labs | List or empty state | [ ] | |
| Tests/packages | Lab tests seeded | Open lab tests/packages | Tabs/cards load | [ ] | |
| Branch order | Items selected | Choose branch, create order | Details open with branch method | [ ] | |
| Home collection | Items selected | Choose home, enter address, create | Address required and saved safely | [ ] | |
| Pay lab order | Order payable | Tap payment CTA | Existing payment UI opens with lab context | [ ] | |
| Result ready | Result uploaded by backend/admin | Open order details | Download CTA appears | [ ] | |
| Download result | Result ready | Tap download | Authorized backend download succeeds or safe foundation message appears | [ ] | |

## Health

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Edit profile | Patient logged in | Health > Profile > Edit | Basic allowed fields save | [ ] | |
| Blood pressure | Patient logged in | Add blood pressure | Record created; no diagnosis language | [ ] | |
| Blood sugar | Patient logged in | Add blood sugar | Record created with context | [ ] | |
| Weight | Patient logged in | Add weight | Record created | [ ] | |
| Vitals list | Records exist | Open Vitals | List/filter works | [ ] | |
| Latest/summary/trends | Records exist | Open Health dashboard | Cards/summary/trends load or empty gracefully | [ ] | |
| Safety disclaimer | Any health page | Inspect top copy | Required disclaimer visible | [ ] | |

## Medications

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Create reminder | Patient logged in | Add medication reminder | Reminder created with safe request | [ ] | |
| Today medications | Reminder scheduled today | Open Today | Grouped schedule appears | [ ] | |
| Mark taken | Today item exists | Tap taken | Backend logs taken, page refreshes | [ ] | |
| Mark skipped | Today item exists | Tap skipped | Backend logs skipped, page refreshes | [ ] | |
| Adherence | Logs exist | Open adherence | Counts/percentage shown as organization only | [ ] | |
| Refill | Refill enabled | Done/skipped | Event recorded; no pharmacy auto-order | [ ] | |

## Care Plans

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| View plans | Plan assigned | Open Care Plans | List or empty state | [ ] | |
| Details | Plan exists | Tap plan | Days/meals/foods/instructions/progress load | [ ] | |
| Check-in | Active plan | Submit check-in | Saved and progress refreshes | [ ] | |
| Meal log | Active plan | Submit meal log | Saved, no calories/progress sent | [ ] | |
| Progress | Plan has activity | Open progress | Commitment-only summary, no treatment claims | [ ] | |

## Notifications

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| List | Notifications exist | Open bell | List or empty state | [ ] | |
| Badge | Unread exist | Open Home | Count badge visible | [ ] | |
| Mark read | Unread item | Tap notification | Details opens, count updates | [ ] | |
| Read all | Unread exist | Tap read all | Count becomes zero | [ ] | |
| Preferences | Authenticated | Open preferences | Toggles save | [ ] | |
| Token foundation | Login/session restore | Observe backend tokens | Local provider token registered if available | [ ] | |

## AI

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Create conversation | AI backend enabled | Open AI, new chat | Conversation created | [ ] | |
| Safe message | Conversation open | Send general organization prompt | Safe response shown | [ ] | |
| Refusal prompt | Conversation open | Ask diagnosis/medication-change prompt | Refusal banner shown | [ ] | |
| Red flag prompt | Conversation open | Send severe symptoms prompt | Emergency banner shown | [ ] | |
| Context preview | Authenticated | Open context preview | Safe summary only, no raw files | [ ] | |
| Toggle context | Conversation open | Toggle context | Backend state updates | [ ] | |
| Provider unavailable | Disable AI provider | Send message | Friendly unavailable state | [ ] | |
| Rate limit | Trigger 429 | Send repeatedly | Rate-limit message shown | [ ] | |

## Account / Legal

| Case | Preconditions | Steps | Expected result | Result | Notes |
| --- | --- | --- | --- | --- | --- |
| Account | Logged in | Open Account | Name/email/role only; no tokens | [ ] | |
| Language | Logged in | Switch Arabic/English | Direction and labels update | [ ] | |
| Legal pages | Logged in | Open each legal page | Draft legal copy visible | [ ] | |
| Support | Logged in | Open Support | Configured contact or safe placeholder | [ ] | |
| About | Logged in | Open About | Version/build/env safely shown | [ ] | |
| Logout | Logged in | Confirm logout | Local session cleared | [ ] | |
