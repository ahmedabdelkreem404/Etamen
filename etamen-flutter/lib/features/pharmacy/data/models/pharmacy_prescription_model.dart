import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_prescription.dart';

class PharmacyPrescriptionModel extends PharmacyPrescription {
  const PharmacyPrescriptionModel({
    required super.id,
    super.fileName,
    super.notes,
    super.createdAt,
  });

  factory PharmacyPrescriptionModel.fromJson(Map<String, dynamic> json) {
    final file = _asMap(json['file']);
    return PharmacyPrescriptionModel(
      id: (json['id'] as num).toInt(),
      fileName: (json['file_name'] ?? file?['original_name'] ?? file?['name'])
          ?.toString(),
      notes: json['notes']?.toString(),
      createdAt: DateTime.tryParse((json['created_at'] ?? '').toString()),
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
