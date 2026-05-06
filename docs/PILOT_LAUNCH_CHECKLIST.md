# Etamen Pilot Launch Checklist

This checklist covers backend, Flutter, and operations for a limited pilot. It is not public-launch approval.

## Backend

- [ ] `APP_DEBUG=false` for staging/pilot.
- [ ] Database migrated.
- [ ] Seeders/admin user ready.
- [ ] Storage private disks configured and tested.
- [ ] Queue worker running.
- [ ] Scheduler cron running.
- [ ] Paymob configured or disabled clearly.
- [ ] Manual payment methods filled with correct instructions.
- [ ] DeepSeek/Gemini configured or AI disabled gracefully.
- [ ] Notification scheduler/status known.
- [ ] Mail/SMS/WhatsApp status known.
- [ ] Backups configured.
- [ ] Domain and SSL configured.
- [ ] Logs monitored.

## Flutter

- [ ] API base URL points to staging/pilot, not local emulator/LAN.
- [ ] Support contacts configured through dart-defines.
- [ ] Legal docs reviewed.
- [ ] Android debug/release pilot APK built.
- [ ] Real device smoke test passed.
- [ ] No secrets in app.
- [ ] Logout/session restore tested.
- [ ] Payment proof upload tested.
- [ ] Lab result download tested.
- [ ] AI refusal/red-flag behavior tested.

## Operations

- [ ] Pilot providers added.
- [ ] Doctor schedules available.
- [ ] Pharmacy products available.
- [ ] Lab tests/packages available.
- [ ] Admin knows how to review manual payments.
- [ ] Pharmacy/lab review workflow is staffed.
- [ ] Support process defined.
- [ ] Refund/manual cancellation process defined.
- [ ] Emergency/medical disclaimer visible.
- [ ] Pilot feedback channel prepared.

## Go / No-Go Rule

Do not invite pilot users until all blocker items are checked or explicitly accepted by the project owner with an owner and rollback plan.
