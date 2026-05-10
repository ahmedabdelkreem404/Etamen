import 'package:etamen_app/features/fitness/data/models/fitness_json_helpers.dart';
import 'package:etamen_app/features/fitness/domain/entities/fitness_entities.dart';

class FitnessBranchSummaryModel extends FitnessBranchSummary {
  const FitnessBranchSummaryModel({
    required super.id,
    super.providerId,
    super.nameAr,
    super.nameEn,
    super.addressAr,
    super.addressEn,
    super.addressLine1,
    super.addressLine2,
    super.district,
    super.cityName,
    super.areaName,
    super.latitude,
    super.longitude,
  });

  factory FitnessBranchSummaryModel.fromJson(Map<String, dynamic> json) {
    final city = asFitnessMap(json['city']);
    final area = asFitnessMap(json['area']);
    return FitnessBranchSummaryModel(
      id: fitnessInt(json['id']) ?? 0,
      providerId: fitnessInt(json['provider_id']),
      nameAr: json['name_ar']?.toString(),
      nameEn: json['name_en']?.toString(),
      addressAr: json['address_ar']?.toString(),
      addressEn: json['address_en']?.toString(),
      addressLine1: json['address_line_1']?.toString(),
      addressLine2: json['address_line_2']?.toString(),
      district: json['district']?.toString(),
      cityName: (json['city_name'] ?? city?['name_ar'] ?? city?['name_en'])
          ?.toString(),
      areaName: (json['area_name'] ?? area?['name_ar'] ?? area?['name_en'])
          ?.toString(),
      latitude: json['latitude']?.toString(),
      longitude: json['longitude']?.toString(),
    );
  }
}

class FitnessProviderSummaryModel extends FitnessProviderSummary {
  const FitnessProviderSummaryModel({
    required super.id,
    required super.nameAr,
    super.nameEn,
    super.type,
    super.phone,
    super.email,
    super.descriptionAr,
    super.descriptionEn,
    super.primaryBranchName,
    super.primaryAreaName,
    super.primaryCityName,
    super.branches,
  });

  factory FitnessProviderSummaryModel.fromJson(Map<String, dynamic> json) {
    return FitnessProviderSummaryModel(
      id: fitnessInt(json['id']) ?? 0,
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      type: json['type']?.toString(),
      phone: json['phone']?.toString(),
      email: json['email']?.toString(),
      descriptionAr: json['description_ar']?.toString(),
      descriptionEn: json['description_en']?.toString(),
      primaryBranchName: json['primary_branch_name']?.toString(),
      primaryAreaName: json['primary_area_name']?.toString(),
      primaryCityName: json['primary_city_name']?.toString(),
      branches: fitnessList(
        json['branches'],
      ).map(FitnessBranchSummaryModel.fromJson).toList(growable: false),
    );
  }
}

class GymModel extends Gym {
  const GymModel({
    required super.id,
    required super.nameAr,
    super.nameEn,
    super.type,
    super.phone,
    super.email,
    super.descriptionAr,
    super.descriptionEn,
    super.primaryBranchName,
    super.primaryAreaName,
    super.primaryCityName,
    super.branches,
    super.menAllowed,
    super.womenAllowed,
    super.ladiesOnlyHours,
    super.hasClasses,
    super.hasPersonalTraining,
    super.membershipPlansCount,
    super.classesCount,
  });

  factory GymModel.fromJson(Map<String, dynamic> json) {
    final profile = asFitnessMap(json['gym_profile']);
    return GymModel(
      id: fitnessInt(json['id']) ?? 0,
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      type: json['type']?.toString(),
      phone: json['phone']?.toString(),
      email: json['email']?.toString(),
      descriptionAr: (profile?['description_ar'] ?? json['description_ar'])
          ?.toString(),
      descriptionEn: (profile?['description_en'] ?? json['description_en'])
          ?.toString(),
      primaryBranchName: json['primary_branch_name']?.toString(),
      primaryAreaName: json['primary_area_name']?.toString(),
      primaryCityName: json['primary_city_name']?.toString(),
      branches: fitnessList(
        json['branches'],
      ).map(FitnessBranchSummaryModel.fromJson).toList(growable: false),
      menAllowed: fitnessBool(profile?['men_allowed'], defaultValue: true),
      womenAllowed: fitnessBool(profile?['women_allowed'], defaultValue: true),
      ladiesOnlyHours: fitnessBool(profile?['ladies_only_hours']),
      hasClasses: fitnessBool(profile?['has_classes']),
      hasPersonalTraining: fitnessBool(profile?['has_personal_training']),
      membershipPlansCount: fitnessInt(json['membership_plans_count']) ?? 0,
      classesCount: fitnessInt(json['classes_count']) ?? 0,
    );
  }
}

class GymMembershipPlanModel extends GymMembershipPlan {
  const GymMembershipPlanModel({
    required super.id,
    required super.providerId,
    required super.nameAr,
    required super.durationDays,
    required super.price,
    super.branchId,
    super.nameEn,
    super.descriptionAr,
    super.descriptionEn,
    super.sessionsCount,
    super.includesClasses,
    super.includesPersonalTraining,
    super.isActive,
    super.branch,
  });

  factory GymMembershipPlanModel.fromJson(Map<String, dynamic> json) {
    final branch = asFitnessMap(json['branch']);
    return GymMembershipPlanModel(
      id: fitnessInt(json['id']) ?? 0,
      providerId: fitnessInt(json['provider_id']) ?? 0,
      branchId: fitnessInt(json['branch_id']),
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      descriptionAr: json['description_ar']?.toString(),
      descriptionEn: json['description_en']?.toString(),
      durationDays: fitnessInt(json['duration_days']) ?? 0,
      price: (json['price'] ?? '0.00').toString(),
      sessionsCount: fitnessInt(json['sessions_count']),
      includesClasses: fitnessBool(json['includes_classes']),
      includesPersonalTraining: fitnessBool(json['includes_personal_training']),
      isActive: fitnessBool(json['is_active'], defaultValue: true),
      branch: branch == null
          ? null
          : FitnessBranchSummaryModel.fromJson(branch),
    );
  }
}

class GymClassModel extends GymClass {
  const GymClassModel({
    required super.id,
    required super.providerId,
    required super.nameAr,
    super.branchId,
    super.coachProviderId,
    super.nameEn,
    super.descriptionAr,
    super.descriptionEn,
    super.startsAt,
    super.endsAt,
    super.capacity,
    super.price,
    super.isActive,
    super.branch,
    super.coach,
  });

  factory GymClassModel.fromJson(Map<String, dynamic> json) {
    final branch = asFitnessMap(json['branch']);
    final coach = asFitnessMap(json['coach']);
    return GymClassModel(
      id: fitnessInt(json['id']) ?? 0,
      providerId: fitnessInt(json['provider_id']) ?? 0,
      branchId: fitnessInt(json['branch_id']),
      coachProviderId: fitnessInt(json['coach_provider_id']),
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      descriptionAr: json['description_ar']?.toString(),
      descriptionEn: json['description_en']?.toString(),
      startsAt: fitnessDate(json['starts_at']),
      endsAt: fitnessDate(json['ends_at']),
      capacity: fitnessInt(json['capacity']),
      price: json['price']?.toString(),
      isActive: fitnessBool(json['is_active'], defaultValue: true),
      branch: branch == null
          ? null
          : FitnessBranchSummaryModel.fromJson(branch),
      coach: coach == null ? null : FitnessProviderSummaryModel.fromJson(coach),
    );
  }
}

class GymBookingModel extends GymBooking {
  const GymBookingModel({
    required super.id,
    required super.status,
    super.bookingNumber,
    super.providerId,
    super.branchId,
    super.membershipPlanId,
    super.gymClassId,
    super.totalAmount,
    super.currency,
    super.paymentId,
    super.startsAt,
    super.endsAt,
    super.notes,
    super.provider,
    super.branch,
    super.membershipPlan,
    super.gymClass,
  });

  factory GymBookingModel.fromJson(Map<String, dynamic> json) {
    final provider = asFitnessMap(json['provider']);
    final branch = asFitnessMap(json['branch']);
    final plan = asFitnessMap(json['membership_plan']);
    final gymClass = asFitnessMap(json['gym_class']);
    final payment = asFitnessMap(json['payment']);
    return GymBookingModel(
      id: fitnessInt(json['id']) ?? 0,
      bookingNumber: json['booking_number']?.toString(),
      providerId: fitnessInt(json['provider_id'] ?? provider?['id']),
      branchId: fitnessInt(json['branch_id'] ?? branch?['id']),
      membershipPlanId: fitnessInt(json['membership_plan_id'] ?? plan?['id']),
      gymClassId: fitnessInt(json['gym_class_id'] ?? gymClass?['id']),
      status: GymBookingStatus.fromWire(json['status']?.toString()),
      totalAmount: json['total_amount']?.toString(),
      currency: (json['currency'] ?? payment?['currency'] ?? 'EGP').toString(),
      paymentId: fitnessInt(json['payment_id'] ?? payment?['id']),
      startsAt: fitnessDate(json['starts_at']),
      endsAt: fitnessDate(json['ends_at']),
      notes: json['notes']?.toString(),
      provider: provider == null
          ? null
          : FitnessProviderSummaryModel.fromJson(provider),
      branch: branch == null
          ? null
          : FitnessBranchSummaryModel.fromJson(branch),
      membershipPlan: plan == null
          ? null
          : GymMembershipPlanModel.fromJson(plan),
      gymClass: gymClass == null ? null : GymClassModel.fromJson(gymClass),
    );
  }
}

class CoachModel extends Coach {
  const CoachModel({
    required super.id,
    required super.nameAr,
    super.nameEn,
    super.type,
    super.phone,
    super.email,
    super.descriptionAr,
    super.descriptionEn,
    super.primaryBranchName,
    super.primaryAreaName,
    super.primaryCityName,
    super.branches,
    super.coachType,
    super.experienceYears,
    super.sessionPrice,
    super.monthlyFollowupPrice,
    super.onlineCoachingEnabled,
    super.gymVisitEnabled,
    super.homeTrainingEnabled,
    super.certificationsSummary,
    super.sessionTypesCount,
    super.availabilityCount,
    super.packagesCount,
  });

  factory CoachModel.fromJson(Map<String, dynamic> json) {
    final profile = asFitnessMap(json['coach_profile']);
    return CoachModel(
      id: fitnessInt(json['id']) ?? 0,
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      type: json['type']?.toString(),
      phone: json['phone']?.toString(),
      email: json['email']?.toString(),
      descriptionAr: json['description_ar']?.toString(),
      descriptionEn: json['description_en']?.toString(),
      primaryBranchName: json['primary_branch_name']?.toString(),
      primaryAreaName: json['primary_area_name']?.toString(),
      primaryCityName: json['primary_city_name']?.toString(),
      branches: fitnessList(
        json['branches'],
      ).map(FitnessBranchSummaryModel.fromJson).toList(growable: false),
      coachType: profile?['coach_type']?.toString() ?? json['type']?.toString(),
      experienceYears: fitnessInt(profile?['experience_years']),
      sessionPrice: profile?['session_price']?.toString(),
      monthlyFollowupPrice: profile?['monthly_followup_price']?.toString(),
      onlineCoachingEnabled: fitnessBool(profile?['online_coaching_enabled']),
      gymVisitEnabled: fitnessBool(profile?['gym_visit_enabled']),
      homeTrainingEnabled: fitnessBool(profile?['home_training_enabled']),
      certificationsSummary: profile?['certifications_summary']?.toString(),
      sessionTypesCount: fitnessInt(json['session_types_count']) ?? 0,
      availabilityCount: fitnessInt(json['availability_count']) ?? 0,
      packagesCount: fitnessInt(json['packages_count']) ?? 0,
    );
  }
}

class CoachSessionTypeModel extends CoachSessionType {
  const CoachSessionTypeModel({
    required super.id,
    required super.providerId,
    required super.nameAr,
    required super.durationMinutes,
    required super.price,
    required super.sessionMode,
    super.nameEn,
    super.descriptionAr,
    super.descriptionEn,
    super.isActive,
  });

  factory CoachSessionTypeModel.fromJson(Map<String, dynamic> json) {
    return CoachSessionTypeModel(
      id: fitnessInt(json['id']) ?? 0,
      providerId: fitnessInt(json['provider_id']) ?? 0,
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      descriptionAr: json['description_ar']?.toString(),
      descriptionEn: json['description_en']?.toString(),
      durationMinutes: fitnessInt(json['duration_minutes']) ?? 0,
      price: (json['price'] ?? '0.00').toString(),
      sessionMode: (json['session_mode'] ?? 'gym').toString(),
      isActive: fitnessBool(json['is_active'], defaultValue: true),
    );
  }
}

class CoachAvailabilitySlotModel extends CoachAvailabilitySlot {
  const CoachAvailabilitySlotModel({
    required super.id,
    required super.providerId,
    required super.status,
    super.startsAt,
    super.endsAt,
  });

  factory CoachAvailabilitySlotModel.fromJson(Map<String, dynamic> json) {
    return CoachAvailabilitySlotModel(
      id: fitnessInt(json['id']) ?? 0,
      providerId: fitnessInt(json['provider_id']) ?? 0,
      startsAt: fitnessDate(json['starts_at']),
      endsAt: fitnessDate(json['ends_at']),
      status: (json['status'] ?? 'unknown').toString(),
    );
  }
}

class CoachPackageModel extends CoachPackage {
  const CoachPackageModel({
    required super.id,
    required super.providerId,
    required super.nameAr,
    required super.sessionsCount,
    required super.price,
    super.nameEn,
    super.descriptionAr,
    super.descriptionEn,
    super.durationDays,
    super.isActive,
  });

  factory CoachPackageModel.fromJson(Map<String, dynamic> json) {
    return CoachPackageModel(
      id: fitnessInt(json['id']) ?? 0,
      providerId: fitnessInt(json['provider_id']) ?? 0,
      nameAr: (json['name_ar'] ?? json['name'] ?? json['name_en'] ?? '')
          .toString(),
      nameEn: json['name_en']?.toString(),
      descriptionAr: json['description_ar']?.toString(),
      descriptionEn: json['description_en']?.toString(),
      sessionsCount: fitnessInt(json['sessions_count']) ?? 0,
      durationDays: fitnessInt(json['duration_days']),
      price: (json['price'] ?? '0.00').toString(),
      isActive: fitnessBool(json['is_active'], defaultValue: true),
    );
  }
}

class CoachBookingModel extends CoachBooking {
  const CoachBookingModel({
    required super.id,
    required super.status,
    super.bookingNumber,
    super.coachProviderId,
    super.sessionTypeId,
    super.availabilitySlotId,
    super.totalAmount,
    super.currency,
    super.paymentId,
    super.patientGoal,
    super.coachProvider,
    super.sessionType,
    super.availabilitySlot,
  });

  factory CoachBookingModel.fromJson(Map<String, dynamic> json) {
    final coach = asFitnessMap(json['coach_provider'] ?? json['provider']);
    final sessionType = asFitnessMap(json['session_type']);
    final slot = asFitnessMap(json['availability_slot']);
    final payment = asFitnessMap(json['payment']);
    return CoachBookingModel(
      id: fitnessInt(json['id']) ?? 0,
      bookingNumber: json['booking_number']?.toString(),
      coachProviderId: fitnessInt(json['coach_provider_id'] ?? coach?['id']),
      sessionTypeId: fitnessInt(json['session_type_id'] ?? sessionType?['id']),
      availabilitySlotId: fitnessInt(
        json['availability_slot_id'] ?? slot?['id'],
      ),
      status: CoachBookingStatus.fromWire(json['status']?.toString()),
      totalAmount: json['total_amount']?.toString(),
      currency: (json['currency'] ?? payment?['currency'] ?? 'EGP').toString(),
      paymentId: fitnessInt(json['payment_id'] ?? payment?['id']),
      patientGoal: json['patient_goal']?.toString(),
      coachProvider: coach == null
          ? null
          : FitnessProviderSummaryModel.fromJson(coach),
      sessionType: sessionType == null
          ? null
          : CoachSessionTypeModel.fromJson(sessionType),
      availabilitySlot: slot == null
          ? null
          : CoachAvailabilitySlotModel.fromJson(slot),
    );
  }
}

class CreateGymBookingRequest {
  const CreateGymBookingRequest({
    required this.providerId,
    this.membershipPlanId,
    this.gymClassId,
    this.notes,
  });

  final int providerId;
  final int? membershipPlanId;
  final int? gymClassId;
  final String? notes;

  Map<String, dynamic> toJson() {
    return {
      'provider_id': providerId,
      if (membershipPlanId != null) 'membership_plan_id': membershipPlanId,
      if (gymClassId != null) 'gym_class_id': gymClassId,
      if (notes?.trim().isNotEmpty == true) 'notes': notes!.trim(),
    };
  }
}

class CreateCoachBookingRequest {
  const CreateCoachBookingRequest({
    required this.coachProviderId,
    required this.sessionTypeId,
    this.availabilitySlotId,
    this.patientGoal,
  });

  final int coachProviderId;
  final int sessionTypeId;
  final int? availabilitySlotId;
  final String? patientGoal;

  Map<String, dynamic> toJson() {
    return {
      'coach_provider_id': coachProviderId,
      'session_type_id': sessionTypeId,
      if (availabilitySlotId != null)
        'availability_slot_id': availabilitySlotId,
      if (patientGoal?.trim().isNotEmpty == true)
        'patient_goal': patientGoal!.trim(),
    };
  }
}
