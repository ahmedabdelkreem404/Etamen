import 'package:etamen_app/features/labs/data/models/lab_test_model.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';

class LabPackageModel extends LabPackage {
  const LabPackageModel({
    required super.id,
    required super.name,
    required super.price,
    required super.currency,
    required super.isActive,
    super.labId,
    super.description,
    super.tests,
    super.sampleTypes,
    super.resultTimeHours,
  });

  factory LabPackageModel.fromJson(Map<String, dynamic> json) {
    final tests = (json['tests'] is List ? json['tests'] as List : const [])
        .whereType<Map>()
        .map(
          (item) => LabTestModel.fromJson(
            item.map((key, value) => MapEntry(key.toString(), value)),
          ),
        )
        .toList(growable: false);

    return LabPackageModel(
      id: (json['id'] as num).toInt(),
      labId: _toInt(
        json['provider_id'] ?? json['lab_provider_id'] ?? json['lab_id'],
      ),
      name:
          _firstString([json['name_ar'], json['name_en'], json['name']]) ??
          'Lab package',
      description:
          (json['description_ar'] ??
                  json['description_en'] ??
                  json['description'])
              ?.toString(),
      price: (json['price'] ?? '0.00').toString(),
      currency: (json['currency'] ?? 'EGP').toString(),
      isActive: json['is_active'] != false,
      tests: tests,
      sampleTypes: _stringList(json['sample_types']),
      resultTimeHours: _toInt(json['result_time_hours']),
    );
  }

  static List<String> _stringList(Object? value) {
    if (value is! List) return const [];
    return value
        .map((item) => item?.toString().trim() ?? '')
        .where((item) => item.isNotEmpty)
        .toList(growable: false);
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
