class Hospital {
  const Hospital({
    required this.id,
    required this.name,
    this.nameAr,
    this.nameEn,
    this.description,
    this.phone,
    this.primaryBranchName,
    this.primaryAreaName,
    this.primaryCityName,
    this.primaryAddress,
    this.latitude,
    this.longitude,
    this.departmentsCount = 0,
    this.doctorsCount = 0,
    this.emergencyAvailable = false,
    this.hasOutpatient = true,
    this.hasInpatient = false,
    this.hasIcu = false,
    this.hasAmbulance = false,
    this.branches = const [],
  });

  final int id;
  final String name;
  final String? nameAr;
  final String? nameEn;
  final String? description;
  final String? phone;
  final String? primaryBranchName;
  final String? primaryAreaName;
  final String? primaryCityName;
  final String? primaryAddress;
  final double? latitude;
  final double? longitude;
  final int departmentsCount;
  final int doctorsCount;
  final bool emergencyAvailable;
  final bool hasOutpatient;
  final bool hasInpatient;
  final bool hasIcu;
  final bool hasAmbulance;
  final List<HospitalBranch> branches;

  String get locationLabel => [
    primaryAreaName,
    primaryCityName,
  ].where((item) => item != null && item.trim().isNotEmpty).join(' - ');
}

class HospitalBranch {
  const HospitalBranch({
    required this.id,
    this.name,
    this.phone,
    this.whatsapp,
    this.address,
    this.district,
    this.city,
    this.area,
    this.latitude,
    this.longitude,
    this.isMain = false,
    this.is24Hours = false,
  });

  final int id;
  final String? name;
  final String? phone;
  final String? whatsapp;
  final String? address;
  final String? district;
  final String? city;
  final String? area;
  final double? latitude;
  final double? longitude;
  final bool isMain;
  final bool is24Hours;
}
