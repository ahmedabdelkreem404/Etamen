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
    this.statusLabelAr,
    this.statusLabelEn,
    this.paymentStatusLabelAr,
    this.paymentStatusLabelEn,
    this.serverCanCancel,
    this.serverCanPay,
    this.serverCanUploadProof,
    this.nextActionKey,
    this.nextActionLabelAr,
    this.nextActionLabelEn,
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
  final String? statusLabelAr;
  final String? statusLabelEn;
  final String? paymentStatusLabelAr;
  final String? paymentStatusLabelEn;
  final bool? serverCanCancel;
  final bool? serverCanPay;
  final bool? serverCanUploadProof;
  final String? nextActionKey;
  final String? nextActionLabelAr;
  final String? nextActionLabelEn;

  bool get canPay {
    if (serverCanPay != null) return serverCanPay!;
    return paymentId != null &&
        (status == PharmacyOrderStatus.accepted ||
            status == PharmacyOrderStatus.awaitingPayment ||
            status == PharmacyOrderStatus.paid ||
            paymentStatus == 'pending_payment' ||
            paymentStatus == 'pending_payment_review');
  }

  bool get canUploadProof {
    if (serverCanUploadProof != null) return serverCanUploadProof!;
    return paymentId != null &&
        (paymentStatus == 'pending_payment' || paymentStatus == 'rejected');
  }

  bool get canCreatePayment {
    if (serverCanPay != null) return serverCanPay! && paymentId == null;
    return paymentId == null &&
        (status == PharmacyOrderStatus.accepted ||
            status == PharmacyOrderStatus.awaitingPayment);
  }

  bool get canCancel {
    if (serverCanCancel != null) return serverCanCancel!;
    return paymentId == null &&
        (paymentStatus == null || paymentStatus == 'unpaid') &&
        (status == PharmacyOrderStatus.pending ||
            status == PharmacyOrderStatus.pharmacyReview ||
            status == PharmacyOrderStatus.accepted ||
            status == PharmacyOrderStatus.awaitingPayment);
  }

  String statusLabel({required bool isArabic}) {
    final fromServer = isArabic ? statusLabelAr : statusLabelEn;
    if (fromServer?.trim().isNotEmpty == true) return fromServer!.trim();
    return status.name;
  }

  String paymentStatusLabel({required bool isArabic}) {
    final fromServer = isArabic ? paymentStatusLabelAr : paymentStatusLabelEn;
    if (fromServer?.trim().isNotEmpty == true) return fromServer!.trim();
    return paymentStatus ?? '-';
  }

  String? nextActionLabel({required bool isArabic}) {
    final fromServer = isArabic ? nextActionLabelAr : nextActionLabelEn;
    return fromServer?.trim().isNotEmpty == true ? fromServer!.trim() : null;
  }
}
