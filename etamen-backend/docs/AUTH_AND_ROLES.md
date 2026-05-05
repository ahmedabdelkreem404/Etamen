# Auth And Roles

## Auth Flow

1. Patient/provider/admin authenticates through Sanctum.
2. Backend derives the current user from the token only.
3. Requests must not trust `user_id`, `patient_user_id`, or `provider_id` for ownership.

## Roles

- `super_admin`
- `admin`
- `patient`
- `doctor`
- `pharmacy_admin`
- `lab_admin`

## Access Rules

- Admin and super admin can manage operational/admin resources.
- Patients can manage only their own medical, medication, care plan, AI, payment, order, appointment, and notification data.
- Provider users can manage only their own approved provider resources.
- Doctor users cannot manage pharmacy/lab resources.
- Pharmacy users cannot manage doctor/lab resources.
- Lab users cannot manage doctor/pharmacy resources.

## Production Notes

- Set `APP_DEBUG=false`.
- Rotate Sanctum tokens on account compromise.
- Protect admin accounts with strong passwords and future MFA.
