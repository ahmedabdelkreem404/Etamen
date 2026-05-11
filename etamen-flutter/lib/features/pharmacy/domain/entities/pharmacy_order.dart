import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order_item.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_prescription.dart';

enum PharmacyOrderStatus {
  pending,
  pharmacyReview,
  accepted,
  awaitingPayment,
  paid,
  preparing,
  ready,
  readyForPickup,
  outForDelivery,
  delivered,
  rejected,
  cancelled,
  unknown;

  static PharmacyOrderStatus fromWire(String? value) {
    return switch (value) {
      'pending' => PharmacyOrderStatus.pending,
      'pharmacy_review' => PharmacyOrderStatus.pharmacyReview,
      'accepted' => PharmacyOrderStatus.accepted,
      'awaiting_payment' => PharmacyOrderStatus.awaitingPayment,
      'paid' => PharmacyOrderStatus.paid,
      'preparing' => PharmacyOrderStatus.preparing,
      'ready' => PharmacyOrderStatus.ready,
      'ready_for_pickup' => PharmacyOrderStatus.readyForPickup,
      'out_for_delivery' => PharmacyOrderStatus.outForDelivery,
      'delivered' => PharmacyOrderStatus.delivered,
      'rejected' => PharmacyOrderStatus.rejected,
      'cancelled' => PharmacyOrderStatus.cancelled,
      _ => PharmacyOrderStatus.unknown,
    };
  }

  bool get isRejectedOrCancelled =>
      this == PharmacyOrderStatus.rejected ||
      this == PharmacyOrderStatus.cancelled;

  bool get isPreparing =>
      this == PharmacyOrderStatus.preparing ||
      this == PharmacyOrderStatus.ready ||
      this == PharmacyOrderStatus.readyForPickup ||
      this == PharmacyOrderStatus.outForDelivery;
}

class PharmacyOrder {
  const PharmacyOrder({
    required this.id,
    required this.status,
    required this.items,
    this.orderNumber,
    this.pharmacyName,
    this.paymentStatus,
    this.paymentId,
    this.subtotal,
    this.discountTotal,
    this.grandTotal,
    this.currency,
    this.createdAt,
    this.prescription,
    this.notes,
  });

  final int id;
  final String? orderNumber;
  final String? pharmacyName;
  final PharmacyOrderStatus status;
  final String? paymentStatus;
  final int? paymentId;
  final String? subtotal;
  final String? discountTotal;
  final String? grandTotal;
  final String? currency;
  final DateTime? createdAt;
  final List<PharmacyOrderItem> items;
  final PharmacyPrescription? prescription;
  final String? notes;

  bool get canPay {
    return paymentId != null &&
        (status == PharmacyOrderStatus.accepted ||
            status == PharmacyOrderStatus.awaitingPayment ||
            status == PharmacyOrderStatus.paid ||
            paymentStatus == 'pending_payment' ||
            paymentStatus == 'pending_payment_review');
  }

  bool get canCreatePayment {
    return paymentId == null &&
        (status == PharmacyOrderStatus.accepted ||
            status == PharmacyOrderStatus.awaitingPayment);
  }

  bool get canCancel {
    return paymentId == null &&
        (paymentStatus == null || paymentStatus == 'unpaid') &&
        (status == PharmacyOrderStatus.pending ||
            status == PharmacyOrderStatus.pharmacyReview ||
            status == PharmacyOrderStatus.accepted ||
            status == PharmacyOrderStatus.awaitingPayment);
  }
}
