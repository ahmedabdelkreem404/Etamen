<?php

return [
    'default_provider' => env('NOTIFICATION_PROVIDER', 'fake'),
    'appointment_reminder_windows' => [24, 1],
    'care_plan_checkin_hour' => env('CARE_PLAN_CHECKIN_REMINDER_HOUR', '20:00'),
];
