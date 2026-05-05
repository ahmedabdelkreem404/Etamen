# File Upload And Download Contract

All sensitive medical and payment files are private. Flutter must use authenticated HTTP requests for uploads and downloads.

## Upload Format

Use `multipart/form-data`.

```http
POST /api/v1/payments/{payment}/proofs
Authorization: Bearer <token>
Content-Type: multipart/form-data
```

Example fields:

| Field | Type | Notes |
| --- | --- | --- |
| `file` | file | Required for upload endpoints. |
| `notes` | string | Optional where supported. |
| `reference_number` | string | Optional for payment proof. |
| `sender_phone` | string | Optional for manual payments. |

## Upload Endpoints

| Endpoint | Role | Allowed files | Max size | Category |
| --- | --- | --- | --- | --- |
| `POST /api/v1/payments/{payment}/proofs` | patient owner | jpg, jpeg, png, pdf | endpoint validation | `payment_proof` |
| `POST /api/v1/pharmacy/prescriptions` | patient | jpg, jpeg, png, pdf | endpoint validation | `prescription` |
| `POST /api/v1/provider/lab/orders/{order}/results` | owning lab/admin | pdf | endpoint validation | `lab_result` |
| `POST /api/v1/care-plans/{plan}/meal-logs` | patient owner | image | 5MB | `meal_photo` |
| `POST /api/v1/provider/documents` | provider owner/staff | document/image | endpoint validation | provider document |

## Download Endpoints

| Endpoint | Auth | Notes |
| --- | --- | --- |
| `GET /api/v1/pharmacy/prescriptions/{prescription}/download` | patient owner, selected pharmacy, admin | No public URL. |
| `GET /api/v1/lab/results/{result}/download` | patient owner, owning lab, admin | No raw path. |

## Response Rules

Resources may return safe metadata:

```json
{
  "file": {
    "id": 12,
    "original_name": "result.pdf",
    "mime_type": "application/pdf",
    "size": 102400,
    "visibility": "private"
  }
}
```

They must not return:

- `path`
- `url`
- `storage_path`
- private disk names in a way that can be used as a public URL

## Flutter Recommendations

- Use Dio multipart upload with progress callbacks.
- Use the authenticated Dio client for downloads.
- On `403`, show no permission.
- On `404`, show file not found or unavailable.
- Do not try `/storage/...` URLs for private files.
