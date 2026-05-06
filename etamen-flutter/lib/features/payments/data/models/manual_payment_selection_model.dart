import 'package:etamen_app/features/payments/domain/entities/manual_payment_selection.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';

class ManualPaymentSelectionModel extends ManualPaymentSelection {
  const ManualPaymentSelectionModel({
    required super.paymentId,
    required super.status,
    required super.methodType,
    super.instructionsAr,
    super.instructionsEn,
  });

  factory ManualPaymentSelectionModel.fromPaymentJson(
    Map<String, dynamic> json,
  ) {
    final method = _asMap(json['payment_method']);

    return ManualPaymentSelectionModel(
      paymentId: (json['id'] as num).toInt(),
      status: PaymentStatusEnum.fromWire(json['status']?.toString()),
      methodType: PaymentMethodType.fromWire(
        (json['method_type'] ?? method?['type'])?.toString(),
      ),
      instructionsAr: (json['instructions_ar'] ?? method?['instructions_ar'])
          ?.toString(),
      instructionsEn: (json['instructions_en'] ?? method?['instructions_en'])
          ?.toString(),
    );
  }

  static Map<String, dynamic>? _asMap(Object? value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, item) => MapEntry(key.toString(), item));
    }
    return null;
  }
}
