# Notifications For Flutter

In-app notifications work without FCM. Push delivery is backend-orchestrated and still requires production provider credentials later.

## Token Registration

`POST /api/v1/notification-tokens`

```json
{
  "token": "device-token",
  "provider": "fcm",
  "device_type": "android",
  "device_name": "Pixel",
  "app_version": "1.0.0",
  "locale": "ar",
  "timezone": "Africa/Cairo"
}
```

On logout, call:

`DELETE /api/v1/notification-tokens/{token}`

## Preferences

`GET /api/v1/notification-preferences`

`PUT /api/v1/notification-preferences`

```json
{
  "preferences": [
    {
      "channel": "push",
      "category": "appointments",
      "is_enabled": true,
      "quiet_hours_start": "22:00",
      "quiet_hours_end": "08:00",
      "timezone": "Africa/Cairo"
    }
  ]
}
```

## In-App Notifications

- `GET /api/v1/notifications`
- `GET /api/v1/notifications/unread-count`
- `POST /api/v1/notifications/{notification}/read`
- `POST /api/v1/notifications/read-all`

## Push Payload Shape

Push payloads should be treated as routing hints only:

```json
{
  "notification_id": 120,
  "category": "appointments",
  "type": "appointment_confirmed",
  "title": "تم تأكيد الموعد",
  "body": "تم تأكيد موعدك.",
  "data": {
    "appointment_id": 55
  }
}
```

Do not expect sensitive medical details in push. Fetch details through authenticated APIs after the user opens the notification.

## Medication Notifications

Medication reminders are organization-only:

"تذكيرات الأدوية هدفها التنظيم فقط، ولا تعتبر وصفة طبية أو نصيحة علاجية."

Flutter local notifications can be added later using backend schedule/queue data. No real FCM live sending is required for integration readiness.
