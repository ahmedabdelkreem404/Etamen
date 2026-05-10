<?php

namespace App\Modules\AdminOperations\Http\Controllers;

use App\Core\Http\ApiController;
use App\Models\User;
use App\Modules\AdminOperations\Http\Resources\AdminAuditLogResource;
use App\Modules\AdminOperations\Http\Resources\AdminPaymentReviewResource;
use App\Modules\AdminOperations\Http\Resources\AdminProviderReviewResource;
use App\Modules\AdminOperations\Http\Resources\DisputeResource;
use App\Modules\AdminOperations\Http\Resources\RefundRequestResource;
use App\Modules\AdminOperations\Http\Resources\SupportTicketResource;
use App\Modules\AdminOperations\Infrastructure\Models\Dispute;
use App\Modules\AdminOperations\Infrastructure\Models\RefundRequest;
use App\Modules\AdminOperations\Infrastructure\Models\SupportTicket;
use App\Modules\Appointments\Infrastructure\Models\Appointment;
use App\Modules\AuditLogs\Application\Services\AuditLogService;
use App\Modules\AuditLogs\Infrastructure\Models\AuditLog;
use App\Modules\Fitness\Infrastructure\Models\CoachBooking;
use App\Modules\Fitness\Infrastructure\Models\GymBooking;
use App\Modules\Payments\Application\Services\ManualPaymentService;
use App\Modules\Payments\Domain\Enums\PaymentStatus;
use App\Modules\Payments\Infrastructure\Models\Payment;
use App\Modules\Providers\Application\Services\ProviderApprovalService;
use App\Modules\Providers\Domain\Enums\ApprovalRequestStatus;
use App\Modules\Providers\Infrastructure\Models\Provider;
use App\Modules\Radiology\Infrastructure\Models\RadiologyOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminOperationsController extends ApiController
{
    public function __construct(
        private readonly ManualPaymentService $manualPaymentService,
        private readonly ProviderApprovalService $providerApprovalService,
        private readonly AuditLogService $auditLogService,
    ) {}

    public function dashboard(Request $request)
    {
        return $this->success([
            'pending_payment_reviews_count' => Payment::query()->where('status', PaymentStatus::PendingReview->value)->count(),
            'pending_provider_approvals_count' => Provider::query()
                ->whereHas('approvalRequests', fn ($query) => $query->where('status', ApprovalRequestStatus::Pending->value))
                ->count(),
            'open_support_tickets_count' => SupportTicket::query()->open()->count(),
            'open_refund_requests_count' => RefundRequest::query()->open()->count(),
            'unresolved_disputes_count' => Dispute::query()->open()->count(),
            'today_appointments_count' => Appointment::query()->whereDate('booked_at', today())->count(),
            'today_radiology_orders_count' => RadiologyOrder::query()->whereDate('created_at', today())->count(),
            'today_gym_bookings_count' => GymBooking::query()->whereDate('created_at', today())->count(),
            'today_coach_bookings_count' => CoachBooking::query()->whereDate('created_at', today())->count(),
            'recent_events' => AdminAuditLogResource::collection($this->auditQuery()->limit(8)->get()),
            'quick_actions' => [
                ['key' => 'payment_reviews', 'label_ar' => 'مراجعة المدفوعات', 'label_en' => 'Payment reviews'],
                ['key' => 'provider_approvals', 'label_ar' => 'موافقات المزودين', 'label_en' => 'Provider approvals'],
                ['key' => 'support_tickets', 'label_ar' => 'تذاكر الدعم', 'label_en' => 'Support tickets'],
                ['key' => 'refund_requests', 'label_ar' => 'طلبات الاسترداد', 'label_en' => 'Refund requests'],
                ['key' => 'disputes', 'label_ar' => 'النزاعات', 'label_en' => 'Disputes'],
                ['key' => 'audit_log', 'label_ar' => 'سجل العمليات', 'label_en' => 'Audit log'],
            ],
        ], 'Admin operations dashboard.');
    }

    public function pendingPayments(Request $request)
    {
        $payments = $this->paymentQuery()
            ->where('status', PaymentStatus::PendingReview->value)
            ->paginate($this->perPage($request, 50));

        return $this->success(AdminPaymentReviewResource::collection($payments), 'Pending payment reviews.');
    }

    public function showPayment(Payment $payment)
    {
        return $this->success(new AdminPaymentReviewResource($payment->load($this->paymentRelations())), 'Payment review details.');
    }

    public function acceptPayment(Request $request, Payment $payment)
    {
        $payment = $this->manualPaymentService->accept($request->user(), $payment);

        $this->auditLogService->log('payment.accepted', $payment, $request->user(), metadata: [
            'payment_id' => $payment->id,
            'payable_type' => class_basename((string) $payment->payable_type),
            'payable_id' => $payment->payable_id,
        ]);

        return $this->success(new AdminPaymentReviewResource($payment->load($this->paymentRelations())), 'Payment accepted.');
    }

    public function rejectPayment(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        $payment = $this->manualPaymentService->reject($request->user(), $payment, $data['reason']);

        $this->auditLogService->log('payment.rejected', $payment, $request->user(), metadata: [
            'payment_id' => $payment->id,
            'reason' => $data['reason'],
        ]);

        return $this->success(new AdminPaymentReviewResource($payment->load($this->paymentRelations())), 'Payment rejected.');
    }

    public function pendingProviders(Request $request)
    {
        $providers = Provider::query()
            ->whereHas('approvalRequests', fn ($query) => $query->where('status', ApprovalRequestStatus::Pending->value))
            ->with($this->providerRelations())
            ->latest()
            ->paginate($this->perPage($request, 50));

        return $this->success(AdminProviderReviewResource::collection($providers), 'Pending provider approvals.');
    }

    public function showProvider(Provider $provider)
    {
        return $this->success(new AdminProviderReviewResource($provider->load($this->providerRelations())), 'Provider review details.');
    }

    public function approveProvider(Request $request, Provider $provider)
    {
        $data = $request->validate(['notes' => ['nullable', 'string', 'max:1000']]);

        $provider = $this->providerApprovalService->approve($provider, $request->user(), $data['notes'] ?? null);

        return $this->success(new AdminProviderReviewResource($provider->load($this->providerRelations())), 'Provider approved.');
    }

    public function rejectProvider(Request $request, Provider $provider)
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);

        $provider = $this->providerApprovalService->reject($provider, $request->user(), $data['notes']);

        return $this->success(new AdminProviderReviewResource($provider->load($this->providerRelations())), 'Provider rejected.');
    }

    public function suspendProvider(Request $request, Provider $provider)
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);

        $provider = $this->providerApprovalService->suspend($provider, $request->user(), $data['notes']);

        return $this->success(new AdminProviderReviewResource($provider->load($this->providerRelations())), 'Provider suspended.');
    }

    public function supportTickets(Request $request)
    {
        $tickets = SupportTicket::query()
            ->with(['user', 'provider', 'assignedAdmin'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('category'), fn ($query, $category) => $query->where('category', $category))
            ->latest()
            ->paginate($this->perPage($request, 50));

        return $this->success(SupportTicketResource::collection($tickets), 'Support tickets.');
    }

    public function showSupportTicket(SupportTicket $ticket)
    {
        return $this->success(new SupportTicketResource($ticket->load(['user', 'provider', 'assignedAdmin', 'messages.sender'])), 'Support ticket details.');
    }

    public function replySupportTicket(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:4000']]);

        $ticket->messages()->create([
            'sender_user_id' => $request->user()->id,
            'sender_type' => 'admin',
            'message' => $data['message'],
            'is_internal_note' => false,
        ]);
        $ticket->update(['status' => SupportTicket::STATUS_PENDING_USER]);
        $this->auditLogService->log('support.ticket.replied', $ticket, $request->user());

        return $this->success(new SupportTicketResource($ticket->refresh()->load(['user', 'provider', 'assignedAdmin', 'messages.sender'])), 'Support ticket replied.');
    }

    public function internalNoteSupportTicket(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate(['message' => ['required', 'string', 'max:4000']]);

        $ticket->messages()->create([
            'sender_user_id' => $request->user()->id,
            'sender_type' => 'admin',
            'message' => $data['message'],
            'is_internal_note' => true,
        ]);
        $this->auditLogService->log('support.ticket.internal_note', $ticket, $request->user());

        return $this->success(new SupportTicketResource($ticket->refresh()->load(['user', 'provider', 'assignedAdmin', 'messages.sender'])), 'Internal note added.');
    }

    public function assignSupportTicket(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate(['assigned_admin_id' => ['nullable', 'integer', 'exists:users,id']]);

        $adminId = $data['assigned_admin_id'] ?? $request->user()->id;
        $admin = User::query()->findOrFail($adminId);
        abort_unless($admin->isPlatformAdmin(), 422, 'Assigned user must be a platform admin.');

        $ticket->update(['assigned_admin_id' => $admin->id]);

        return $this->success(new SupportTicketResource($ticket->refresh()->load(['user', 'provider', 'assignedAdmin'])), 'Support ticket assigned.');
    }

    public function closeSupportTicket(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate(['message' => ['nullable', 'string', 'max:4000']]);

        if (! empty($data['message'])) {
            $ticket->messages()->create([
                'sender_user_id' => $request->user()->id,
                'sender_type' => 'admin',
                'message' => $data['message'],
                'is_internal_note' => false,
            ]);
        }

        $ticket->update(['status' => SupportTicket::STATUS_CLOSED, 'closed_at' => now()]);
        $this->auditLogService->log('support.ticket.closed', $ticket, $request->user());

        return $this->success(new SupportTicketResource($ticket->refresh()->load(['user', 'provider', 'assignedAdmin', 'messages.sender'])), 'Support ticket closed.');
    }

    public function refunds(Request $request)
    {
        $refunds = RefundRequest::query()
            ->with(['user', 'payment'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate($this->perPage($request, 50));

        return $this->success(RefundRequestResource::collection($refunds), 'Refund requests.');
    }

    public function showRefund(RefundRequest $refund)
    {
        return $this->success(new RefundRequestResource($refund->load(['user', 'payment'])), 'Refund request details.');
    }

    public function markRefundUnderReview(Request $request, RefundRequest $refund)
    {
        return $this->updateRefundStatus($request, $refund, RefundRequest::STATUS_UNDER_REVIEW, 'refund.under_review', 'Refund marked under review.');
    }

    public function approveRefund(Request $request, RefundRequest $refund)
    {
        return $this->updateRefundStatus($request, $refund, RefundRequest::STATUS_APPROVED, 'refund.approved', 'Refund approved.');
    }

    public function rejectRefund(Request $request, RefundRequest $refund)
    {
        return $this->updateRefundStatus($request, $refund, RefundRequest::STATUS_REJECTED, 'refund.rejected', 'Refund rejected.');
    }

    public function markRefundProcessed(Request $request, RefundRequest $refund)
    {
        return $this->updateRefundStatus($request, $refund, RefundRequest::STATUS_PROCESSED, 'refund.processed', 'Refund processed.');
    }

    public function disputes(Request $request)
    {
        $disputes = Dispute::query()
            ->with(['user', 'provider', 'payment'])
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate($this->perPage($request, 50));

        return $this->success(DisputeResource::collection($disputes), 'Disputes.');
    }

    public function showDispute(Dispute $dispute)
    {
        return $this->success(new DisputeResource($dispute->load(['user', 'provider', 'payment'])), 'Dispute details.');
    }

    public function assignDispute(Request $request, Dispute $dispute)
    {
        $data = $request->validate(['assigned_admin_id' => ['nullable', 'integer', 'exists:users,id']]);

        $adminId = $data['assigned_admin_id'] ?? $request->user()->id;
        $admin = User::query()->findOrFail($adminId);
        abort_unless($admin->isPlatformAdmin(), 422, 'Assigned user must be a platform admin.');

        $dispute->update([
            'assigned_admin_id' => $admin->id,
            'status' => Dispute::STATUS_INVESTIGATING,
        ]);

        return $this->success(new DisputeResource($dispute->refresh()->load(['user', 'provider', 'payment'])), 'Dispute assigned.');
    }

    public function resolveDispute(Request $request, Dispute $dispute)
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);

        $dispute->update(['status' => Dispute::STATUS_RESOLVED, 'resolved_at' => now()]);
        $this->auditLogService->log('dispute.resolved', $dispute, $request->user(), metadata: ['notes' => $data['notes']]);

        return $this->success(new DisputeResource($dispute->refresh()->load(['user', 'provider', 'payment'])), 'Dispute resolved.');
    }

    public function closeDispute(Request $request, Dispute $dispute)
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);

        $dispute->update(['status' => Dispute::STATUS_CLOSED, 'resolved_at' => $dispute->resolved_at ?? now()]);
        $this->auditLogService->log('dispute.closed', $dispute, $request->user(), metadata: ['notes' => $data['notes']]);

        return $this->success(new DisputeResource($dispute->refresh()->load(['user', 'provider', 'payment'])), 'Dispute closed.');
    }

    public function auditLog(Request $request)
    {
        $logs = $this->auditQuery()
            ->when($request->query('event'), fn ($query, $event) => $query->where('action', $event))
            ->paginate($this->perPage($request, 50));

        return $this->success(AdminAuditLogResource::collection($logs), 'Audit log.');
    }

    private function updateRefundStatus(Request $request, RefundRequest $refund, string $status, string $event, string $message)
    {
        $data = $request->validate(['notes' => ['required', 'string', 'max:1000']]);

        $refund->update([
            'status' => $status,
            'admin_note' => $data['notes'],
            'resolved_by' => $request->user()->id,
            'resolved_at' => in_array($status, [RefundRequest::STATUS_APPROVED, RefundRequest::STATUS_REJECTED, RefundRequest::STATUS_PROCESSED], true)
                ? now()
                : $refund->resolved_at,
        ]);

        $this->auditLogService->log($event, $refund, $request->user(), metadata: ['notes' => $data['notes']]);

        return $this->success(new RefundRequestResource($refund->refresh()->load(['user', 'payment'])), $message);
    }

    private function paymentQuery()
    {
        return Payment::query()
            ->with($this->paymentRelations())
            ->orderByDesc('created_at');
    }

    private function paymentRelations(): array
    {
        return ['user', 'provider', 'paymentMethod', 'payable', 'invoice', 'proofs.file'];
    }

    private function providerRelations(): array
    {
        return ['owner', 'approvalRequests', 'documents'];
    }

    private function auditQuery()
    {
        return AuditLog::query()
            ->with('actor')
            ->latest();
    }
}
