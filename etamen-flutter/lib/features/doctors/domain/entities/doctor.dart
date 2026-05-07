class Doctor {
  const Doctor({
    required this.id,
    required this.name,
    required this.isActive,
    this.bio,
    this.avatarUrl,
    this.ratingAverage,
    this.reviewsCount = 0,
    this.doctorProfileId,
    this.consultationFee,
    this.yearsOfExperience,
    this.specialties = const [],
    this.branches = const [],
  });

  final int id;
  final String name;
  final bool isActive;
  final String? bio;
  final String? avatarUrl;
  final double? ratingAverage;
  final int reviewsCount;
  final int? doctorProfileId;
  final String? consultationFee;
  final int? yearsOfExperience;
  final List<String> specialties;
  final List<String> branches;
}
