import 'package:etamen_app/features/radiology/data/models/radiology_json_helpers.dart';
import 'package:etamen_app/features/radiology/domain/entities/radiology_order.dart';

class RadiologyOrderItemModel extends RadiologyOrderItem {
  const RadiologyOrderItemModel({
    required super.id,
    required super.scanId,
    required super.scanNameAr,
    super.scanNameEn,
    super.categoryNameAr,
    super.categoryNameEn,
    super.unitPrice,
    super.quantity,
    super.totalPrice,
    super.preparationSnapshotAr,
    super.preparationSnapshotEn,
  });

  factory RadiologyOrderItemModel.fromJson(Map<String, dynamic> json) {
    return RadiologyOrderItemModel(
      id: radiologyInt(json['id']) ?? 0,
      scanId: radiologyInt(json['radiology_scan_id'] ?? json['scan_id']) ?? 0,
      scanNameAr:
          (json['scan_name_ar'] ??
                  json['name_ar'] ??
                  json['scan_name_en'] ??
                  '')
              .toString(),
      scanNameEn: json['scan_name_en']?.toString(),
      categoryNameAr: json['category_name_ar']?.toString(),
      categoryNameEn: json['category_name_en']?.toString(),
      unitPrice: json['unit_price']?.toString(),
      quantity: radiologyInt(json['quantity']) ?? 1,
      totalPrice: json['total_price']?.toString(),
      preparationSnapshotAr: json['preparation_snapshot_ar']?.toString(),
      preparationSnapshotEn: json['preparation_snapshot_en']?.toString(),
    );
  }
}
