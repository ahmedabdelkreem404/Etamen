# Queue And Scheduler Operations

## Queue Worker

Run a worker in production:

```bash
php artisan queue:work --tries=3 --backoff=10
```

Use a process supervisor such as Supervisor or systemd.

## Scheduler

Recommended cron:

```cron
* * * * * cd /path/to/etamen-backend && php artisan schedule:run >> /dev/null 2>&1
```

## Operational Jobs

Sprint 11 added job foundations for:

- Notification dispatches.
- Appointment reminders.
- Medication notifications.
- Care plan check-in reminders.
- Lab result notifications.
- Payment review notifications.
- AI safety admin alerts.

Medication missed-dose jobs already live in the Medications module and should be scheduled when the platform is ready.

## Failed Jobs

Inspect failed jobs:

```bash
php artisan queue:failed
php artisan queue:retry all
```

## Readiness

Admin readiness endpoint:

```http
GET /api/v1/system/readiness
```

It checks database, cache, private storage, and scheduler run status.
