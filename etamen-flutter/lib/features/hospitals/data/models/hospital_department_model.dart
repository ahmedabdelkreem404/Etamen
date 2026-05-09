import 'package:etamen_app/features/hospitals/domain/entities/hospital_department.dart';

class HospitalDepartmentModel extends HospitalDepartment {
  const HospitalDepartmentModel({
    required super.id,
    required super.name,
    super.nameAr,
    super.nameEn,
    super.description,
    super.doctorsCount,
  });

  factory HospitalDepartmentModel.fromJson(Map<String, dynamic> json) {
    return HospitalDepartmentModel(
      id: (json['id'] as num).toInt(),
      name:
          _firstString([json['name_ar'], json['name_en'], json['name']]) ??
          'Department',
      nameAr: _stringOrNull(json['name_ar']),
      nameEn: _stringOrNull(json['name_en']),
      description: _firstString([
        json['description_ar'],
        json['description_en'],
      ]),
      doctorsCount: _intOrZero(json['doctors_count']),
    );
  }

  static String? _firstString(List<Object?> values) {
    for (final value in values) {
      final text = value?.toString().trim();
      if (text != null && text.isNotEmpty && text != 'null') return text;
    }
    return null;
  }

  static String? _stringOrNull(Object? value) {
    final text = value?.toString().trim();
    if (text == null || text.isEmpty || text == 'null') return null;
    return text;
  }

  static int _intOrZero(Object? value) {
    if (value is num) return value.toInt();
    return int.tryParse(value?.toString() ?? '') ?? 0;
  }
}
