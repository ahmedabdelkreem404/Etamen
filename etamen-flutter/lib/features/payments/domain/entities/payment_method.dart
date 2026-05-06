enum PaymentMethodType {
  paymob,
  manualVodafoneCash,
  manualInstapay,
  unknown;

  static PaymentMethodType fromWire(String? value) {
    return switch (value) {
      'paymob' => PaymentMethodType.paymob,
      'manual_vodafone_cash' => PaymentMethodType.manualVodafoneCash,
      'manual_instapay' => PaymentMethodType.manualInstapay,
      _ => PaymentMethodType.unknown,
    };
  }

  String get wireValue {
    return switch (this) {
      PaymentMethodType.paymob => 'paymob',
      PaymentMethodType.manualVodafoneCash => 'manual_vodafone_cash',
      PaymentMethodType.manualInstapay => 'manual_instapay',
      PaymentMethodType.unknown => 'unknown',
    };
  }

  bool get isManual =>
      this == PaymentMethodType.manualVodafoneCash ||
      this == PaymentMethodType.manualInstapay;
}

class PaymentMethod {
  const PaymentMethod({
    required this.id,
    required this.type,
    required this.nameAr,
    required this.nameEn,
    required this.isActive,
    this.instructionsAr,
    this.instructionsEn,
  });

  final int id;
  final PaymentMethodType type;
  final String nameAr;
  final String nameEn;
  final String? instructionsAr;
  final String? instructionsEn;
  final bool isActive;

  String displayName(bool isArabic) {
    if (isArabic && nameAr.isNotEmpty) return nameAr;
    if (!isArabic && nameEn.isNotEmpty) return nameEn;
    return nameAr.isNotEmpty ? nameAr : nameEn;
  }

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
