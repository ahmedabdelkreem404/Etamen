# Auth And Session Contract

Etamen mobile authentication uses Laravel Sanctum bearer tokens.

## Authorization Header

```http
Authorization: Bearer <token>
Accept: application/json
```

Flutter must store tokens in secure storage such as `flutter_secure_storage`. Do not store tokens in plain shared preferences.

## Register

`POST /api/v1/auth/register`

```json
{
  "name": "Patient Name",
  "email": "patient@example.com",
  "phone": "01012345678",
  "password": "password",
  "password_confirmation": "password"
}
```

Success:

```json
{
  "success": true,
  "message": "Registered successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Patient Name",
      "email": "patient@example.com",
      "roles": ["patient"]
    },
    "token": "plain-text-sanctum-token",
    "token_type": "Bearer"
  }
}
```

## Login

`POST /api/v1/auth/login`

```json
{
  "email": "patient@example.com",
  "password": "password"
}
```

Success has the same `user`, `token`, and `token_type` shape as register.

Invalid credentials return `401` with the standard error envelope.

## Me

`GET /api/v1/me`

Returns the authenticated user resource, including roles. Use this endpoint after app launch to validate the token.

## Logout

`POST /api/v1/auth/logout`

Revokes the current access token.

## Refresh Tokens

Refresh tokens are not implemented. Flutter should treat `401` as token invalid/expired and ask the user to log in again.

## Role Notes

Routes enforce roles through backend middleware and policies:

- `patient` routes require a patient user.
- `provider` routes require provider staff/owner context.
- `admin` routes require `admin` or `super_admin`.
- Flutter must never show a screen only because the UI role says so; backend authorization remains the source of truth.
