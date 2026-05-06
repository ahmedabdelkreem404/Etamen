import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';

class ManualPaymentSelection {
  const ManualPaymentSelection({
    required this.paymentId,
    required this.status,
    required this.methodType,
    this.instructionsAr,
    this.instructionsEn,
  });

  final int paymentId;
  final PaymentStatusEnum status;
  final PaymentMethodType methodType;
  final String? instructionsAr;
  final String? instructionsEn;

  String? instructions(bool isArabic) {
    if (isArabic && instructionsAr != null && instructionsAr!.isNotEmpty) {
      return instructionsAr;
    }
    if (!isArabic && instructionsEn != null && instructionsEn!.isNotEmpty) {
      return instructionsEn;
    }
    return instructionsAr?.isNotEmpty == true ? instructionsAr : instructionsEn;
  }
}
