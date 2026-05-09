# Sprint 45 Local Radiology Flutter QA

Date: 2026-05-09

Scope: local emulator only. This sprint did not touch Hostinger, `etamen.inolty.com`, SSH, or deployment.

## Flutter Screens Added

- Services entry for Radiology.
- Radiology catalog home with scan categories and available scans.
- Radiology order builder with selected scans and backend-price safety copy.
- Radiology order details with payment, items, preparation notes, safe result metadata, and result download action.
- My radiology orders page.
- Radiology-aware payment navigation through the existing manual payment flow.

## APIs Consumed

- `GET /api/v1/radiology/scan-categories`
- `GET /api/v1/radiology/scans`
- `GET /api/v1/radiology/orders`
- `POST /api/v1/radiology/orders`
- `GET /api/v1/radiology/orders/{id}`
- `POST /api/v1/radiology/orders/{id}/cancel`
- `GET /api/v1/radiology/orders/{id}/results`
- `GET /api/v1/radiology/results/{id}/download`
- `GET /api/v1/payment-methods`
- `POST /api/v1/payments/{payment}/manual/select`
- `POST /api/v1/payments/{payment}/proofs`
- `GET /api/v1/payments/{payment}/status`

## Local Backend Prep

Commands run locally:

```text
php artisan migrate:fresh --seed
php artisan db:seed --class=PilotDemoSeeder
php artisan etamen:ensure-payment-methods --staging
php artisan serve --host=0.0.0.0 --port=8000
```

Endpoint checks:

| Endpoint | Result |
| --- | --- |
| `/api/v1/system/health` | PASS, HTTP 200 |
| `/api/v1/radiology/scan-categories` | PASS, 10 categories |
| `/api/v1/radiology/scans` | PASS, 7 scans |
| `/api/v1/payment-methods` | PASS, Vodafone Cash + InstaPay active, Paymob hidden |

No raw private path or payment config was exposed in the checked patient APIs.

## Emulator QA Result

Device: Android emulator `emulator-5554`.

API base:

```text
http://10.0.2.2:8000/api/v1
```

Flow result:

| Step | Result |
| --- | --- |
| Login | PASS |
| Services entry | PASS |
| Radiology catalog/categories | PASS |
| Scan list | PASS |
| Order builder | PASS |
| Create order | PASS |
| Payment methods | PASS |
| Select Vodafone Cash | PASS |
| Upload proof image through emulator picker | PASS |
| Pending review state | PASS |
| Admin accepts same payment locally | PASS |
| Flutter status refresh shows paid | PASS |
| Admin uploads visible private result | PASS |
| Patient sees result metadata | PASS |
| Download action | PASS, app showed successful download message |
| Logout | PASS |

Local order used for QA:

```text
RAD-20260509-UZ5Y7N3S
```

## Payment / Admin Review

Manual proof upload moved the payment to `pending_review` and the radiology order to `pending_payment_review`.

Admin local API accept moved:

```text
payment: verified
radiology order: paid
```

After a visible result upload, the order moved to:

```text
result_ready
```

Flutter showed the safe result card and did not display raw storage paths.

## Result Privacy

Checked patient response for:

- `medical_private`
- `storage/private`
- raw `path`
- raw `disk`

Result: no leak markers found in the patient order response.

## Screenshots

```text
I:\Etamen\.tmp\sprint45-local-radiology\
```

Captured:

- `01-services-radiology-entry.png`
- `02-radiology-home.png`
- `03-radiology-categories.png`
- `04-scan-list.png`
- `05-order-builder.png`
- `06-order-details-pending-payment.png`
- `07-payment-methods.png`
- `08-proof-upload.png`
- `09-pending-review.png`
- `10-after-admin-accept-paid.png`
- `11-result-visible.png`
- `12-result-download-or-metadata.png`
- `13-logout.png`

## APK

```text
I:\Etamen\.tmp\etamen-local-radiology.apk
C:\Users\Ahmed Abdelkareem\OneDrive\Desktop\Etamen_Android_Website_Ready\etamen-local-radiology.apk
```

SHA-256:

```text
E0EAB943839F0862722EB985573DA0831CE2B6563FEE063B0F5BA8CA7800D0AD
```

## Tests / Build

Final status for Sprint 45:

```text
Backend: php artisan test -> PASS
Flutter: flutter analyze -> PASS
Flutter: flutter test -> PASS
Flutter: android-x64 debug APK build -> PASS
```

Note: final APK build used local Gradle cache via `GRADLE_USER_HOME=I:\Etamen\.gradle` to avoid network dependency during the build.

## Remaining Blockers

- This is local emulator proof only.
- No staging deployment was touched or approved.
- No real phone radiology proof upload was tested.
- No public-launch readiness is claimed.
- Result download is basic local-app download/open behavior; production file handling still needs broader device QA.

## Decision

```text
LOCAL_RADIOLOGY_FLUTTER_ACCEPTED
```

This accepts the local Flutter radiology patient flow only. It does not approve staging, physical-device pilot, or public launch.
