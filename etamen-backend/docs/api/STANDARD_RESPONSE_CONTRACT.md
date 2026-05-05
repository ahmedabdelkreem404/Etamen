# Standard Response Contract

This contract applies to `/api/v1/*` endpoints and is the shape Flutter should model globally.

## Success

```json
{
  "success": true,
  "message": "Human readable message.",
  "data": {}
}
```

`data` may be an object, an array, `null`, or a Laravel resource collection object. Flutter should always read `success` first, then parse `data` according to the endpoint-specific DTO.

## Error

```json
{
  "success": false,
  "message": "Human readable error.",
  "data": null,
  "errors": {}
}
```

Validation errors use `errors` keyed by field name:

```json
{
  "success": false,
  "message": "The given data was invalid.",
  "data": null,
  "errors": {
    "email": ["The email field is required."]
  }
}
```

## Status Codes

| Code | Meaning | Flutter handling |
| --- | --- | --- |
| 200 | OK | Parse `data`. |
| 201 | Created | Parse `data` and update local state. |
| 400 | Bad request | Show generic request error. |
| 401 | Unauthenticated | Clear secure token and route to login. |
| 403 | Forbidden | Show no-permission screen/message. |
| 404 | Not found | Show not-found/empty-state message. |
| 422 | Validation failed | Bind field errors to form inputs. |
| 429 | Rate limited | Show too-many-requests message and retry later. |
| 500 | Server error | Show generic server error. Never show stack traces. |

## Security Notes

- API responses must not expose raw file paths, API keys, provider secrets, payment gateway raw payloads, wallet internals to patients, AI provider raw payloads, or private medical file content.
- Flutter must never infer payment success from redirect screens; only backend status endpoints are trusted.
- Flutter must never send ownership fields such as `user_id`, `patient_user_id`, `provider_id`, `source`, `flag`, or financial amounts unless an endpoint explicitly documents the field as safe input.
