import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order_item.dart';

class PharmacyOrderItemModel extends PharmacyOrderItem {
  const PharmacyOrderItemModel({
    required super.productName,
    required super.quantity,
    required super.unitPrice,
    required super.lineTotal,
    super.productId,
  });

  factory PharmacyOrderItemModel.fromJson(Map<String, dynamic> json) {
    return PharmacyOrderItemModel(
      productId: _toInt(json['product_id']),
      productName: (json['product_name'] ?? json['name'] ?? 'Product')
          .toString(),
      quantity: _toInt(json['quantity']) ?? 1,
      unitPrice: (json['unit_price'] ?? json['price'] ?? '0.00').toString(),
      lineTotal: (json['line_total'] ?? '0.00').toString(),
    );
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }
}
