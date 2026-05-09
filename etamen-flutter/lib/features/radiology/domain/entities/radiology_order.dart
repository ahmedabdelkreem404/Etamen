import 'package:etamen_app/features/radiology/domain/entities/radiology_provider_summary.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_result.dart';

enum RadiologyOrderStatus {
  pendingPayment,
  pendingPaymentReview,
  paid,
  accepted,
  inProgress,
  resultReady,
  completed,
  cancelledByPatient,
  cancelledByProvider,
  rejected,
  unknown;

  static RadiologyOrderStatus fromWire(String? value) {
    return switch (value) {
      'pending_payment' => RadiologyOrderStatus.pendingPayment,
      'pending_payment_review' => RadiologyOrderStatus.pendingPaymentReview,
      'paid' => RadiologyOrderStatus.paid,
      'accepted' => RadiologyOrderStatus.accepted,
      'in_progress' => RadiologyOrderStatus.inProgress,
      'result_ready' => RadiologyOrderStatus.resultReady,
      'completed' => RadiologyOrderStatus.completed,
      'cancelled_by_patient' => RadiologyOrderStatus.cancelledByPatient,
      'cancelled_by_provider' => RadiologyOrderStatus.cancelledByProvider,
      'rejected' => RadiologyOrderStatus.rejected,
      _ => RadiologyOrderStatus.unknown,
    };
  }

  String get wireValue {
    return switch (this) {
      RadiologyOrderStatus.pendingPayment => 'pending_payment',
      RadiologyOrderStatus.pendingPaymentReview => 'pending_payment_review',
      RadiologyOrderStatus.paid => 'paid',
      RadiologyOrderStatus.accepted => 'accepted',
      RadiologyOrderStatus.inProgress => 'in_progress',
      RadiologyOrderStatus.resultReady => 'result_ready',
      RadiologyOrderStatus.completed => 'completed',
      RadiologyOrderStatus.cancelledByPatient => 'cancelled_by_patient',
      RadiologyOrderStatus.cancelledByProvider => 'cancelled_by_provider',
      RadiologyOrderStatus.rejected => 'rejected',
      RadiologyOrderStatus.unknown => 'unknown',
    };
  }

  String friendlyLabel(bool isArabic) {
    if (!isArabic) {
      return switch (this) {
        RadiologyOrderStatus.pendingPayment => 'Awaiting payment',
        RadiologyOrderStatus.pendingPaymentReview => 'Payment under review',
        RadiologyOrderStatus.paid => 'Paid',
        RadiologyOrderStatus.accepted => 'Accepted',
        RadiologyOrderStatus.inProgress => 'In progress',
        RadiologyOrderStatus.resultReady => 'Result ready',
        RadiologyOrderStatus.completed => 'Completed',
        RadiologyOrderStatus.cancelledByPatient => 'Cancelled by you',
        RadiologyOrderStatus.cancelledByProvider => 'Cancelled by center',
        RadiologyOrderStatus.rejected => 'Rejected',
        RadiologyOrderStatus.unknown => 'Unknown',
      };
    }

    return switch (this) {
      RadiologyOrderStatus.pendingPayment => 'في انتظار الدفع',
      RadiologyOrderStatus.pendingPaymentReview => 'جاري مراجعة الدفع',
      RadiologyOrderStatus.paid => 'تم الدفع',
      RadiologyOrderStatus.accepted => 'تم قبول الطلب',
      RadiologyOrderStatus.inProgress => 'جاري التنفيذ',
      RadiologyOrderStatus.resultReady => 'النتيجة جاهزة',
      RadiologyOrderStatus.completed => 'مكتمل',
      RadiologyOrderStatus.cancelledByPatient => 'ملغي بواسطتك',
      RadiologyOrderStatus.cancelledByProvider => 'ملغي من المركز',
      RadiologyOrderStatus.rejected => 'مرفوض',
      RadiologyOrderStatus.unknown => 'حالة غير معروفة',
    };
  }

  bool get canPay {
    return this == RadiologyOrderStatus.pendingPayment ||
        this == RadiologyOrderStatus.pendingPaymentReview ||
        this == RadiologyOrderStatus.paid ||
        this == RadiologyOrderStatus.rejected;
  }

  bool get hasResult {
    return this == RadiologyOrderStatus.resultReady ||
        this == RadiologyOrderStatus.completed;
  }
}

class RadiologyOrderItem {
  const RadiologyOrderItem({
    required this.id,
    required this.scanId,
    required this.scanNameAr,
    this.scanNameEn,
    this.categoryNameAr,
    this.categoryNameEn,
    this.unitPrice,
    this.quantity = 1,
    this.totalPrice,
    this.preparationSnapshotAr,
    this.preparationSnapshotEn,
  });

  final int id;
  final int scanId;
  final String scanNameAr;
  final String? scanNameEn;
  final String? categoryNameAr;
  final String? categoryNameEn;
  final String? unitPrice;
  final int quantity;
  final String? totalPrice;
  final String? preparationSnapshotAr;
  final String? preparationSnapshotEn;

  String scanName(bool isArabic) {
    if (!isArabic && scanNameEn?.trim().isNotEmpty == true) {
      return scanNameEn!.trim();
    }
    return scanNameAr;
  }

  String? categoryName(bool isArabic) {
    if (!isArabic && categoryNameEn?.trim().isNotEmpty == true) {
      return categoryNameEn!.trim();
    }
    return categoryNameAr;
  }

  String? preparation(bool isArabic) {
    if (!isArabic && preparationSnapshotEn?.trim().isNotEmpty == true) {
      return preparationSnapshotEn!.trim();
    }
    return preparationSnapshotAr;
  }
}

class RadiologyOrder {
  const RadiologyOrder({
    required this.id,
    required this.status,
    required this.items,
    this.orderNumber,
    this.providerId,
    this.provider,
    this.branchId,
    this.branch,
    this.subtotal,
    this.discountAmount,
    this.totalAmount,
    this.currency = 'EGP',
    this.paymentId,
    this.patientNotes,
    this.scheduledAt,
    this.createdAt,
    this.results = const [],
  });

  final int id;
  final String? orderNumber;
  final int? providerId;
  final RadiologyProviderSummary? provider;
  final int? branchId;
  final RadiologyBranchSummary? branch;
  final RadiologyOrderStatus status;
  final String? subtotal;
  final String? discountAmount;
  final String? totalAmount;
  final String currency;
  final int? paymentId;
  final String? patientNotes;
  final DateTime? scheduledAt;
  final DateTime? createdAt;
  final List<RadiologyOrderItem> items;
  final List<RadiologyResult> results;

  bool get hasVisibleResults => results.isNotEmpty;

  bool get canContinuePayment => paymentId != null && status.canPay;
}
