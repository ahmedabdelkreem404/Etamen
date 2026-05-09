class HospitalDepartment {
  const HospitalDepartment({
    required this.id,
    required this.name,
    this.nameAr,
    this.nameEn,
    this.description,
    this.doctorsCount = 0,
  });

  final int id;
  final String name;
  final String? nameAr;
  final String? nameEn;
  final String? description;
  final int doctorsCount;
}
