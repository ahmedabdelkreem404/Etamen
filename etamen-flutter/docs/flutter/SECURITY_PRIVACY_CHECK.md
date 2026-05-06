# Etamen Flutter Security & Privacy Check

Sprint 25 re-runs a Flutter-side safety audit for pilot readiness. This check does not replace backend security review.

## Search Terms Checked

- `Paymob secret`
- `HMAC`
- `DeepSeek`
- `Gemini`
- `API key`
- `FCM server key`
- `server key`
- `hardcoded token`
- `hardcoded user_id`
- `patient_user_id`
- `provider_id`
- `payment verified`
- `mark paid`
- `diagnosis claim`
- `treatment guarantee`
- `cure guarantee`
- `raw file path`
- `private path`
- `admin endpoint`
- `admin/`

## Findings

| Area | Result | Notes |
| --- | --- | --- |
| Mobile secrets | No blocking finding | No Paymob/AI/FCM/server secrets are required by the Flutter app. |
| Auth tokens | Ready | Runtime token is stored through secure storage; no committed token found in app code. |
| Payment verification | Ready | Flutter can upload proof/open checkout/poll status, but never verifies payment or marks paid. |
| Patient ownership | Ready | Patient create requests avoid ownership ids; models may parse backend-returned IDs. |
| Admin/provider endpoints | Ready | Patient app code uses patient/public endpoints only. Docs may mention admin endpoints as forbidden or manual operations. |
| Private files | Ready | Upload/download flows go through backend endpoints; UI avoids exposing backend raw storage paths. |
| Medical claims | Ready with legal caveat | The app contains disclaimers and safety warnings; it does not claim diagnosis, cure, or treatment success. |
| AI privacy | Ready | AI request DTOs do not send raw context/system/provider data; metadata sanitizer hides unsafe keys. |

## Sprint 25 Actual Search Result

The final search found only allowed references:

- UI localization key `paymentVerified`, used as a display label for backend-confirmed status.
- Sanitizer blocklists containing `hmac` and related unsafe keys.
- Tests using fake `deepseek`, `gemini`, `server key`, `hmac_secret`, and `payment verified` strings to prove unsafe fields are removed or backend-returned histories are parsed safely.
- Documentation warning that secrets, admin endpoints, raw file paths, and private data must never be exposed by Flutter.

No blocking Flutter code finding was found for committed mobile secrets, hardcoded auth tokens, admin endpoint calls, direct payment verification, raw private path display, or public medical diagnosis/treatment claims.

## Allowed Findings

The following are allowed and should not be treated as pilot blockers:

- Documentation warning that secrets must never be stored in Flutter.
- Tests asserting forbidden fields such as `patient_user_id`, `source`, `status`, `api_key`, or `provider_response` are absent.
- DTO/model parsing backend-returned fields such as provider IDs, statuses, flags, or metadata.
- Sanitizer blocklists containing unsafe key names.
- Legal/safety disclaimer wording that mentions diagnosis, treatment, medication, refunds, or secrets as warnings.

## Issues Fixed In Sprint 25

- Android main manifest now includes `INTERNET` permission, not only debug/profile manifests.
- Android app label changed to `Etamen`.
- Android NDK pinned to installed `27.0.12077973` for real build compatibility.
- Splash launch background aligned to Etamen cream color.
- Fallback `ErrorMapper` messages use user-friendly Arabic text for common HTTP/network cases.

## Remaining Warnings

- Release signing is not configured with a real private keystore.
- Default Flutter launcher icon remains; replace before public launch.
- Real FCM/APNS production push is intentionally not configured.
- Legal/support text is a pilot draft and needs professional/legal review before public launch.
- Pilot data and operational review processes must be prepared on the backend/admin side.
