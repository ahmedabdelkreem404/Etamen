import 'package:etamen_app/features/hospitals/domain/entities/hospital.dart';

class HospitalModel extends Hospital {
  const HospitalModel({
    required super.id,
    required super.name,
    super.nameAr,
    super.nameEn,
    super.description,
    super.phone,
    super.primaryBranchName,
    super.primaryAreaName,
    super.primaryCityName,
    super.primaryAddress,
    super.latitude,
    super.longitude,
    super.departmentsCount,
    super.doctorsCount,
    super.emergencyAvailable,
    super.hasOutpatient,
    super.hasInpatient,
    super.hasIcu,
    super.hasAmbulance,
    super.branches,
  });

  factory HospitalModel.fromJson(Map<String, dynamic> json) {
    final branches = (json['branches'] as List? ?? const [])
        .whereType<Map>()
        .map((item) => HospitalBranchModel.fromJson(_asMap(item)))
        .toList(growable: false);

    return HospitalModel(
      id: (json['id'] as num).toInt(),
      name:
          _firstString([
            json['name_ar'],
            json['name_en'],
            json['name'],
            json['display_name'],
          ]) ??
          'Hospital',
      nameAr: _stringOrNull(json['name_ar']),
      nameEn: _stringOrNull(json['name_en']),
      description: _firstString([
        json['description_ar'],
        json['description_en'],
        json['description'],
      ]),
      phone: _stringOrNull(json['phone']),
      primaryBranchName: _stringOrNull(json['primary_branch_name']),
      primaryAreaName: _stringOrNull(json['primary_area_name']),
      primaryCityName: _stringOrNull(json['primary_city_name']),
      primaryAddress: _stringOrNull(json['primary_address']),
      latitude: _doubleOrNull(json['latitude']),
      longitude: _doubleOrNull(json['longitude']),
      departmentsCount: _intOrZero(json['departments_count']),
      doctorsCount: _intOrZero(json['doctors_count']),
      emergencyAvailable: json['emergency_available'] == true,
      hasOutpatient: json['has_outpatient'] != false,
      hasInpatient: json['has_inpatient'] == true,
      hasIcu: json['has_icu'] == true,
      hasAmbulance: json['has_ambulance'] == true,
      branches: branches,
    );
  }
}

class HospitalBranchModel extends HospitalBranch {
  const HospitalBranchModel({
    required super.id,
    super.name,
    super.phone,
    super.whatsapp,
    super.address,
    super.district,
    super.city,
    super.area,
    super.latitude,
    super.longitude,
    super.isMain,
    super.is24Hours,
  });

  factory HospitalBranchModel.fromJson(Map<String, dynamic> json) {
    final city = _mapOrNull(json['city']);
    final area = _mapOrNull(json['area']);

    return HospitalBranchModel(
      id: (json['id'] as num).toInt(),
      name: _firstString([json['name_ar'], json['name_en']]),
      phone: _stringOrNull(json['phone']),
      whatsapp: _stringOrNull(json['whatsapp']),
      address: _firstString([
        json['address_ar'],
        json['address_en'],
        json['address_line_1'],
      ]),
      district: _stringOrNull(json['district']),
      city: _firstString([city?['name_ar'], city?['name_en']]),
      area: _firstString([area?['name_ar'], area?['name_en']]),
      latitude: _doubleOrNull(json['latitude']),
      longitude: _doubleOrNull(json['longitude']),
      isMain: json['is_main'] == true,
      is24Hours: json['is_24_hours'] == true,
    );
  }
}

Map<String, dynamic> _asMap(Map<dynamic, dynamic> value) {
  return value.map((key, item) => MapEntry(key.toString(), item));
}

Map<String, dynamic>? _mapOrNull(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) return _asMap(value);
  return null;
}

String? _firstString(List<Object?> values) {
  for (final value in values) {
    final text = value?.toString().trim();
    if (text != null && text.isNotEmpty && text != 'null') return text;
  }
  return null;
}

String? _stringOrNull(Object? value) {
  final text = value?.toString().trim();
  if (text == null || text.isEmpty || text == 'null') return null;
  return text;
}

double? _doubleOrNull(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  return double.tryParse(value.toString());
}

int _intOrZero(Object? value) {
  if (value is num) return value.toInt();
  return int.tryParse(value?.toString() ?? '') ?? 0;
}
