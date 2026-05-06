import 'package:etamen_app/features/labs/domain/entities/lab_order_item.dart';

class LabOrderItemModel extends LabOrderItem {
  const LabOrderItemModel({
    required super.itemType,
    required super.itemName,
    required super.quantity,
    required super.unitPrice,
    required super.lineTotal,
    super.testId,
    super.packageId,
  });

  factory LabOrderItemModel.fromJson(Map<String, dynamic> json) {
    return LabOrderItemModel(
      itemType: (json['item_type'] ?? 'test').toString(),
      testId: _toInt(json['test_id']),
      packageId: _toInt(json['package_id']),
      itemName: (json['item_name'] ?? json['name'] ?? 'Lab item').toString(),
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
