enum PaymentStatus {
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

  static PaymentStatus fromWire(String? value) {
    return switch (value) {
      'draft' => PaymentStatus.draft,
      'awaiting_method' => PaymentStatus.awaitingMethod,
      'awaiting_proof' => PaymentStatus.awaitingProof,
      'pending_review' => PaymentStatus.pendingReview,
      'pending_gateway' => PaymentStatus.pendingGateway,
      'verified' => PaymentStatus.verified,
      'rejected' => PaymentStatus.rejected,
      'failed' => PaymentStatus.failed,
      'expired' => PaymentStatus.expired,
      'cancelled' => PaymentStatus.cancelled,
      'refunded' => PaymentStatus.refunded,
      _ => PaymentStatus.unknown,
    };
  }
}

class PaymentSummary {
  const PaymentSummary({
    required this.id,
    required this.amount,
    required this.currency,
    required this.status,
  });

  final int id;
  final String amount;
  final String currency;
  final PaymentStatus status;
}
