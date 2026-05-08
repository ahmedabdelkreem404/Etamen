# Radiology Catalog Foundation

Date: 2026-05-08

Sprint 37 starts the radiology vertical as a backend/admin catalog foundation only. It does not make radiology a patient-ready product surface.

## Scope Implemented

- `radiology_scan_categories` taxonomy with stable codes, Arabic-first labels, English labels, active flag, and sort order.
- `radiology_scans` catalog owned by approved radiology providers and optionally scoped to one provider branch.
- `radiology_preparation_instructions` for general scan/category preparation text with required Arabic and English disclaimers.
- Provider-side protected APIs for a radiology provider owner/staff to manage their own scans.
- Admin-side protected APIs for scan categories, scans, and preparation instructions.
- Filament resources for scan categories, scans, and preparation instructions.
- Safe read-only catalog endpoints for future discovery, returning only approved active radiology providers and active scans.
- Pilot demo seed data for one staging/local radiology center, branch, scan catalog, and preparation instructions.

## Taxonomy Seeded

| Code | Arabic | English |
| --- | --- | --- |
| `x_ray` | أشعة عادية | X-Ray |
| `ultrasound` | موجات فوق صوتية | Ultrasound |
| `ct_scan` | أشعة مقطعية | CT Scan |
| `mri` | رنين مغناطيسي | MRI |
| `mammography` | ماموجرام | Mammography |
| `doppler` | دوبلر | Doppler |
| `echo` | إيكو | Echo |
| `ecg` | رسم قلب | ECG |
| `dental_panorama` | بانوراما أسنان | Dental Panorama |
| `dexa` | قياس كثافة العظام | DEXA Scan |

## API Surface

Public safe read-only endpoints:

- `GET /api/v1/radiology/scan-categories`
- `GET /api/v1/radiology/scans`
- `GET /api/v1/radiology/preparation-instructions`

Provider protected endpoints:

- `GET /api/v1/provider/radiology/scans`
- `POST /api/v1/provider/radiology/scans`
- `GET /api/v1/provider/radiology/scans/{scan}`
- `PATCH /api/v1/provider/radiology/scans/{scan}`
- `DELETE /api/v1/provider/radiology/scans/{scan}` deactivates instead of hard-deleting.

Admin protected endpoints:

- `GET|POST|PATCH|DELETE /api/v1/admin/radiology-scan-categories`
- `GET|POST|PATCH|DELETE /api/v1/admin/radiology-scans`
- `GET|POST|PATCH|DELETE /api/v1/admin/radiology-preparation-instructions`

## Safety Rules

- Only providers of type `radiology` can own radiology scans.
- Suspended providers cannot manage scans.
- Branch IDs must belong to the same radiology provider.
- Patients cannot create, update, or delete radiology scans.
- Inactive scans are hidden from safe public listing.
- Unapproved radiology providers are hidden from safe public listing.
- Public resources do not include private provider documents, national ID files, bank files, admin notes, or raw storage paths.
- Preparation instructions are general information only and automatically keep the disclaimer:
  - Arabic: "هذه تعليمات عامة ولا تغني عن تعليمات المركز أو الطبيب."
  - English: "These are general instructions and do not replace the center or doctor instructions."

## Internal-Only Boundaries

Radiology remains admin/internal foundation. Flutter does not expose a radiology patient module yet.

Not implemented in Sprint 37:

- Patient-facing Flutter radiology screens.
- Radiology orders.
- Radiology payments.
- Radiology result/report delivery.
- DICOM/image delivery.
- Radiology appointment lifecycle.
- Public launch operations.

## Future Integration Notes

Radiology scans currently remain their own catalog table. They can later be linked into `provider_services` as `service_type = radiology_scan` when the order/payment/result lifecycle is implemented and price ownership is fully tested end to end.
