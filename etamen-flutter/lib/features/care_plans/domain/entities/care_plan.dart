class CarePlan {
  const CarePlan({
    required this.id,
    required this.title,
    required this.planType,
    required this.status,
    this.description,
    this.goalText,
    this.startDate,
    this.endDate,
    this.visibility,
    this.source,
    this.providerName,
    this.assignedByName,
    this.safetyDisclaimer,
    this.checkinsCount,
    this.mealLogsCount,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final String title;
  final CarePlanType planType;
  final CarePlanStatus status;
  final String? description;
  final String? goalText;
  final String? startDate;
  final String? endDate;
  final String? visibility;
  final String? source;
  final String? providerName;
  final String? assignedByName;
  final String? safetyDisclaimer;
  final int? checkinsCount;
  final int? mealLogsCount;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  bool get isActive => status == CarePlanStatus.active;

  bool get isInactive =>
      status == CarePlanStatus.paused ||
      status == CarePlanStatus.completed ||
      status == CarePlanStatus.cancelled;
}

enum CarePlanType {
  nutrition('nutrition'),
  generalCare('general_care'),
  weightManagement('weight_management'),
  diabetesFollowup('diabetes_followup'),
  bloodPressureFollowup('blood_pressure_followup'),
  fitnessFollowup('fitness_followup'),
  recoveryFollowup('recovery_followup'),
  other('other'),
  unknown('unknown');

  const CarePlanType(this.wireValue);

  final String wireValue;

  static CarePlanType fromWire(Object? value) {
    final normalized = value?.toString();
    return CarePlanType.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => CarePlanType.unknown,
    );
  }
}

enum CarePlanStatus {
  draft('draft'),
  active('active'),
  paused('paused'),
  completed('completed'),
  cancelled('cancelled'),
  unknown('unknown');

  const CarePlanStatus(this.wireValue);

  final String wireValue;

  static CarePlanStatus fromWire(Object? value) {
    final normalized = value?.toString();
    return CarePlanStatus.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => CarePlanStatus.unknown,
    );
  }
}
