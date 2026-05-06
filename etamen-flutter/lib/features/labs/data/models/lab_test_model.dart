import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';

class LabTestModel extends LabTest {
  const LabTestModel({
    required super.id,
    required super.name,
    required super.price,
    required super.currency,
    required super.isActive,
    super.labId,
    super.nameAr,
    super.nameEn,
    super.description,
    super.sampleType,
    super.preparationInstructions,
    super.resultTimeHours,
  });

  factory LabTestModel.fromJson(Map<String, dynamic> json) {
    return LabTestModel(
      id: (json['id'] as num).toInt(),
      labId: _toInt(
        json['provider_id'] ?? json['lab_provider_id'] ?? json['lab_id'],
      ),
      name:
          _firstString([json['name_ar'], json['name_en'], json['name']]) ??
          'Lab test',
      nameAr: json['name_ar']?.toString(),
      nameEn: json['name_en']?.toString(),
      description:
          (json['description_ar'] ??
                  json['description_en'] ??
                  json['description'])
              ?.toString(),
      price: (json['price'] ?? '0.00').toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      sampleType: json['sample_type']?.toString(),
      preparationInstructions:
          (json['preparation_instructions_ar'] ??
                  json['preparation_instructions_en'] ??
                  json['preparation_instructions'])
              ?.toString(),
      resultTimeHours: _toInt(json['result_time_hours'] ?? json['result_time']),
      isActive: json['is_active'] != false,
    );
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
