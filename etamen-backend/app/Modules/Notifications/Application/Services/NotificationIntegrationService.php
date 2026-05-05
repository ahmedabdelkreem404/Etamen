<?php

namespace App\Modules\Notifications\Application\Services;

use App\Models\User;
use App\Modules\AI\Domain\Enums\AiSafetySeverity;
use App\Modules\AI\Infrastructure\Models\AiSafetyEvent;
use App\Modules\Labs\Infrastructure\Models\LabResult;
use App\Modules\Notifications\Domain\Enums\NotificationCategory;
use App\Modules\Notifications\Domain\Enums\NotificationPriority;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Pharmacies\Infrastructure\Models\PharmacyOrder;
use App\Modules\Wallets\Infrastructure\Models\WithdrawalRequest;

class NotificationIntegrationService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function notifyLabResultReady(LabResult $result): void
    {
        $order = $result->order;

        if (! $order?->patient) {
            return;
        }

        $this->notifications->sendToUser($order->patient, 'lab_result_ready', [
            'order_number' => $order->order_number,
        ], [
            'category' => NotificationCategory::Labs,
            'priority' => NotificationPriority::High,
            'idempotency_key' => 'lab_result:'.$result->id.':ready',
            'data' => [
                'lab_order_id' => $order->id,
                'lab_result_id' => $result->id,
            ],
        ]);
    }

    public function notifyPharmacyOrderStatus(PharmacyOrder $order): void
    {
        if (! $order->patient) {
            return;
        }

        $template = match ($order->order_status->value) {
            'accepted' => 'pharmacy_order_accepted',
            'rejected' => 'pharmacy_order_rejected',
            'paid' => 'pharmacy_order_paid',
            'delivered' => 'pharmacy_order_delivered',
            default => 'pharmacy_order_created',
        };

        $this->notifications->sendToUser($order->patient, $template, [
            'order_number' => $order->order_number,
        ], [
            'category' => NotificationCategory::Pharmacy,
            'idempotency_key' => 'pharmacy_order:'.$order->id.':'.$order->order_status->value,
            'data' => [
                'pharmacy_order_id' => $order->id,
                'status' => $order->order_status->value,
            ],
        ]);
    }

    public function notifyPaymentRejected(Payment $payment): void
    {
        if (! $payment->user) {
            return;
        }

        $this->notifications->sendToUser($payment->user, 'payment_rejected', [
            'payment_id' => $payment->id,
        ], [
            'category' => NotificationCategory::Payments,
            'priority' => NotificationPriority::High,
            'idempotency_key' => 'payment:'.$payment->id.':rejected',
            'data' => [
                'payment_id' => $payment->id,
                'status' => $payment->status->value,
            ],
        ]);
    }

    public function notifyWithdrawalStatus(WithdrawalRequest $withdrawal): void
    {
        if (! $withdrawal->requester) {
            return;
        }

        $template = match ($withdrawal->status->value) {
            'approved' => 'withdrawal_approved',
            'rejected' => 'withdrawal_rejected',
            'paid' => 'withdrawal_paid',
            default => 'withdrawal_requested',
        };

        $this->notifications->sendToUser($withdrawal->requester, $template, [
            'withdrawal_id' => $withdrawal->id,
        ], [
            'category' => NotificationCategory::Wallet,
            'idempotency_key' => 'withdrawal:'.$withdrawal->id.':'.$withdrawal->status->value,
            'data' => [
                'withdrawal_id' => $withdrawal->id,
                'status' => $withdrawal->status->value,
            ],
        ]);
    }

    public function notifyAiSafetyEvent(AiSafetyEvent $event): int
    {
        if (! in_array($event->severity, [AiSafetySeverity::High, AiSafetySeverity::Critical], true)) {
            return 0;
        }

        $count = 0;
        User::role(['admin', 'super_admin'])->get()->each(function (User $admin) use ($event, &$count): void {
            $this->notifications->sendToUser($admin, 'ai_red_flag_admin_alert', [
                'event_id' => $event->id,
            ], [
                'category' => NotificationCategory::AiSafety,
                'priority' => NotificationPriority::Urgent,
                'critical' => true,
                'idempotency_key' => 'ai_safety_event:'.$event->id.':admin_alert:'.$admin->id,
                'data' => [
                    'ai_safety_event_id' => $event->id,
                    'severity' => $event->severity->value,
                ],
            ]);
            $count++;
        });

        return $count;
    }
}
