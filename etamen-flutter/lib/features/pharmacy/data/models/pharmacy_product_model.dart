import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';

class PharmacyProductModel extends PharmacyProduct {
  const PharmacyProductModel({
    required super.id,
    required super.name,
    required super.price,
    required super.currency,
    required super.requiresPrescription,
    required super.isActive,
    super.pharmacyId,
    super.nameAr,
    super.nameEn,
    super.description,
    super.imageUrl,
    super.stockStatus,
    super.stockQuantity,
    super.serverInStock,
    super.stockLabelAr,
    super.stockLabelEn,
    super.category,
  });

  factory PharmacyProductModel.fromJson(Map<String, dynamic> json) {
    final category = _asMap(json['category']);
    return PharmacyProductModel(
      id: (json['id'] as num).toInt(),
      pharmacyId: _toInt(
        json['provider_id'] ??
            json['pharmacy_provider_id'] ??
            json['pharmacy_id'],
      ),
      name:
          _firstString([json['name_ar'], json['name_en'], json['name']]) ??
          'Product',
      nameAr: json['name_ar']?.toString(),
      nameEn: json['name_en']?.toString(),
      description:
          (json['description_ar'] ??
                  json['description_en'] ??
                  json['description'])
              ?.toString(),
      price: (json['price'] ?? '0.00').toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      requiresPrescription: json['requires_prescription'] == true,
      isActive: json['is_active'] != false,
      imageUrl: (json['image_url'] ?? json['image'])?.toString(),
      stockStatus: (json['stock_status'] ?? json['stock_quantity'])?.toString(),
      stockQuantity: _toInt(json['stock_quantity']),
      serverInStock: json['in_stock'] is bool ? json['in_stock'] as bool : null,
      stockLabelAr: json['stock_label_ar']?.toString(),
      stockLabelEn: json['stock_label_en']?.toString(),
      category:
          (category?['name_ar'] ?? category?['name_en'] ?? json['category'])
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

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }

  static String? _firstString(List<Object?> values) {
    for (final value in values) {
      final text = value?.toString().trim();
      if (text != null && text.isNotEmpty && text != 'null') return text;
    }
    return null;
  }
}
