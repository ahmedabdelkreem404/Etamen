import 'package:etamen_app/features/payments/domain/entities/payment_method.dart';

class PaymentMethodModel extends PaymentMethod {
  const PaymentMethodModel({
    required super.id,
    required super.type,
    required super.nameAr,
    required super.nameEn,
    required super.isActive,
    super.instructionsAr,
    super.instructionsEn,
  });

  factory PaymentMethodModel.fromJson(Map<String, dynamic> json) {
    return PaymentMethodModel(
      id: (json['id'] as num).toInt(),
      type: PaymentMethodType.fromWire(json['type']?.toString()),
      nameAr: (json['name_ar'] ?? json['name'] ?? '').toString(),
      nameEn: (json['name_en'] ?? json['name'] ?? '').toString(),
      instructionsAr: json['instructions_ar']?.toString(),
      instructionsEn: json['instructions_en']?.toString(),
      isActive: json['is_active'] != false,
    );
  }
}
