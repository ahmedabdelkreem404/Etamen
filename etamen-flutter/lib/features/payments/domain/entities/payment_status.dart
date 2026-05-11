enum PaymentStatusEnum {
  draft,
  awaitingMethod,
  awaitingProof,
  pendingReview,
  pendingGateway,
  verified,
  rejected,
  failed,
  expired,
  cancelled,
  refunded,
  unknown;

  static PaymentStatusEnum fromWire(String? value) {
    return switch (value) {
      'draft' => PaymentStatusEnum.draft,
      'awaiting_method' => PaymentStatusEnum.awaitingMethod,
      'awaiting_proof' => PaymentStatusEnum.awaitingProof,
      'pending_review' => PaymentStatusEnum.pendingReview,
      'pending_gateway' => PaymentStatusEnum.pendingGateway,
      'verified' => PaymentStatusEnum.verified,
      'rejected' => PaymentStatusEnum.rejected,
      'failed' => PaymentStatusEnum.failed,
      'expired' => PaymentStatusEnum.expired,
      'cancelled' => PaymentStatusEnum.cancelled,
      'refunded' => PaymentStatusEnum.refunded,
      _ => PaymentStatusEnum.unknown,
    };
  }

  String get wireValue {
    return switch (this) {
      PaymentStatusEnum.draft => 'draft',
      PaymentStatusEnum.awaitingMethod => 'awaiting_method',
      PaymentStatusEnum.awaitingProof => 'awaiting_proof',
      PaymentStatusEnum.pendingReview => 'pending_review',
      PaymentStatusEnum.pendingGateway => 'pending_gateway',
      PaymentStatusEnum.verified => 'verified',
      PaymentStatusEnum.rejected => 'rejected',
      PaymentStatusEnum.failed => 'failed',
      PaymentStatusEnum.expired => 'expired',
      PaymentStatusEnum.cancelled => 'cancelled',
      PaymentStatusEnum.refunded => 'refunded',
      PaymentStatusEnum.unknown => 'unknown',
    };
  }

  bool get isTerminal {
    return switch (this) {
      PaymentStatusEnum.verified ||
      PaymentStatusEnum.rejected ||
      PaymentStatusEnum.failed ||
      PaymentStatusEnum.expired ||
      PaymentStatusEnum.cancelled ||
      PaymentStatusEnum.refunded => true,
      _ => false,
    };
  }

  bool get shouldPoll {
    return this == PaymentStatusEnum.pendingReview ||
        this == PaymentStatusEnum.pendingGateway;
  }
}

class PaymentStatusDetails {
  const PaymentStatusDetails({
    required this.id,
    required this.status,
    required this.amount,
    required this.currency,
    required this.payableType,
    required this.payableId,
    this.methodType,
    this.appointmentStatus,
    this.radiologyOrderStatus,
    this.pharmacyOrderStatus,
    this.pharmacyPaymentStatus,
    this.labOrderStatus,
    this.labPaymentStatus,
    this.gymBookingStatus,
    this.coachBookingStatus,
    this.invoice,
    this.rejectionReason,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final PaymentStatusEnum status;
  final String amount;
  final String currency;
  final String? methodType;
  final String? payableType;
  final int? payableId;
  final String? appointmentStatus;
  final String? radiologyOrderStatus;
  final String? pharmacyOrderStatus;
  final String? pharmacyPaymentStatus;
  final String? labOrderStatus;
  final String? labPaymentStatus;
  final String? gymBookingStatus;
  final String? coachBookingStatus;
  final Map<String, dynamic>? invoice;
  final String? rejectionReason;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  bool get canUploadProof {
    return status == PaymentStatusEnum.awaitingProof ||
        status == PaymentStatusEnum.rejected;
  }

  bool get canRetry {
    return status == PaymentStatusEnum.rejected ||
        status == PaymentStatusEnum.failed ||
        status == PaymentStatusEnum.expired ||
        status == PaymentStatusEnum.cancelled;
  }
}
