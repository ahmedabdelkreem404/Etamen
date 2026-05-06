import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';

class LabResultModel extends LabResult {
  const LabResultModel({
    required super.id,
    super.labOrderId,
    super.fileName,
    super.status,
    super.uploadedAt,
    super.notes,
  });

  factory LabResultModel.fromJson(Map<String, dynamic> json) {
    final file = _asMap(json['file']);
    return LabResultModel(
      id: (json['id'] as num).toInt(),
      labOrderId: _toInt(json['order_id'] ?? json['lab_order_id']),
      fileName:
          (json['file_name'] ??
                  json['title_ar'] ??
                  json['title_en'] ??
                  file?['original_name'] ??
                  file?['name'])
              ?.toString(),
      status: (json['status'] ?? json['result_status'])?.toString(),
      notes: json['notes']?.toString(),
      uploadedAt: DateTime.tryParse(
        (json['created_at'] ?? json['uploaded_at'] ?? '').toString(),
      ),
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
}
