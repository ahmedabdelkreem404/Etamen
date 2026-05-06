import 'package:etamen_app/features/labs/domain/entities/lab_order_item.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';

enum LabOrderStatus {
  labReview,
  accepted,
  awaitingPayment,
  paid,
  sampleScheduled,
  sampleCollected,
  sampleCollection,
  processing,
  resultReady,
  completed,
  rejected,
  cancelled,
  unknown;

  static LabOrderStatus fromWire(String? value) {
    return switch (value) {
      'lab_review' => LabOrderStatus.labReview,
      'accepted' => LabOrderStatus.accepted,
      'awaiting_payment' => LabOrderStatus.awaitingPayment,
      'paid' => LabOrderStatus.paid,
      'sample_scheduled' => LabOrderStatus.sampleScheduled,
      'sample_collected' => LabOrderStatus.sampleCollected,
      'sample_collection' => LabOrderStatus.sampleCollection,
      'processing' => LabOrderStatus.processing,
      'result_ready' => LabOrderStatus.resultReady,
      'completed' => LabOrderStatus.completed,
      'rejected' => LabOrderStatus.rejected,
      'cancelled' => LabOrderStatus.cancelled,
      _ => LabOrderStatus.unknown,
    };
  }

  bool get isInProgress {
    return this == LabOrderStatus.sampleScheduled ||
        this == LabOrderStatus.sampleCollected ||
        this == LabOrderStatus.sampleCollection ||
        this == LabOrderStatus.processing;
  }

  bool get isRejectedOrCancelled =>
      this == LabOrderStatus.rejected || this == LabOrderStatus.cancelled;
}

class LabOrder {
  const LabOrder({
    required this.id,
    required this.status,
    required this.items,
    this.orderNumber,
    this.labName,
    this.paymentStatus,
    this.paymentId,
    this.subtotal,
    this.grandTotal,
    this.currency,
    this.sampleCollectionMethod,
    this.homeAddress,
    this.createdAt,
    this.results = const [],
    this.notes,
  });

  final int id;
  final String? orderNumber;
  final String? labName;
  final LabOrderStatus status;
  final String? paymentStatus;
  final int? paymentId;
  final String? subtotal;
  final String? grandTotal;
  final String? currency;
  final String? sampleCollectionMethod;
  final String? homeAddress;
  final DateTime? createdAt;
  final List<LabOrderItem> items;
  final List<LabResult> results;
  final String? notes;

  bool get hasResult => results.isNotEmpty;

  bool get canPay {
    return paymentId != null &&
        (status == LabOrderStatus.accepted ||
            status == LabOrderStatus.awaitingPayment ||
            status == LabOrderStatus.paid ||
            paymentStatus == 'pending_payment' ||
            paymentStatus == 'pending_payment_review' ||
            paymentStatus == 'rejected');
  }

  bool get canCreatePayment {
    return paymentId == null &&
        (status == LabOrderStatus.accepted ||
            status == LabOrderStatus.awaitingPayment);
  }
}
