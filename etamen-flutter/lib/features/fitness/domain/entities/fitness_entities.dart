class FitnessBranchSummary {
  const FitnessBranchSummary({
    required this.id,
    this.providerId,
    this.nameAr,
    this.nameEn,
    this.addressAr,
    this.addressEn,
    this.addressLine1,
    this.addressLine2,
    this.district,
    this.cityName,
    this.areaName,
    this.latitude,
    this.longitude,
  });

  final int id;
  final int? providerId;
  final String? nameAr;
  final String? nameEn;
  final String? addressAr;
  final String? addressEn;
  final String? addressLine1;
  final String? addressLine2;
  final String? district;
  final String? cityName;
  final String? areaName;
  final String? latitude;
  final String? longitude;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    if (nameAr?.trim().isNotEmpty == true) return nameAr!.trim();
    return isArabic ? 'الفرع الرئيسي' : 'Main branch';
  }

  String address(bool isArabic) {
    final localized = !isArabic && addressEn?.trim().isNotEmpty == true
        ? addressEn
        : addressAr;
    final parts = [localized, addressLine1, district, areaName, cityName]
        .where((item) => item?.trim().isNotEmpty == true)
        .map((item) => item!.trim())
        .toList(growable: false);
    return parts.join(' - ');
  }
}

class FitnessProviderSummary {
  const FitnessProviderSummary({
    required this.id,
    required this.nameAr,
    this.nameEn,
    this.type,
    this.phone,
    this.email,
    this.descriptionAr,
    this.descriptionEn,
    this.primaryBranchName,
    this.primaryAreaName,
    this.primaryCityName,
    this.branches = const [],
  });

  final int id;
  final String nameAr;
  final String? nameEn;
  final String? type;
  final String? phone;
  final String? email;
  final String? descriptionAr;
  final String? descriptionEn;
  final String? primaryBranchName;
  final String? primaryAreaName;
  final String? primaryCityName;
  final List<FitnessBranchSummary> branches;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    return nameAr;
  }

  String? description(bool isArabic) {
    if (!isArabic && descriptionEn?.trim().isNotEmpty == true) {
      return descriptionEn!.trim();
    }
    return descriptionAr?.trim().isEmpty == true ? null : descriptionAr;
  }

  String get locationLabel {
    final parts = [primaryAreaName, primaryCityName]
        .where((item) => item?.trim().isNotEmpty == true)
        .map((item) => item!.trim())
        .toList(growable: false);
    return parts.join(' - ');
  }

  FitnessBranchSummary? get primaryBranch {
    if (branches.isEmpty) return null;
    return branches.first;
  }
}

class Gym extends FitnessProviderSummary {
  const Gym({
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
    this.menAllowed = true,
    this.womenAllowed = true,
    this.ladiesOnlyHours = false,
    this.hasClasses = false,
    this.hasPersonalTraining = false,
    this.membershipPlansCount = 0,
    this.classesCount = 0,
  });

  final bool menAllowed;
  final bool womenAllowed;
  final bool ladiesOnlyHours;
  final bool hasClasses;
  final bool hasPersonalTraining;
  final int membershipPlansCount;
  final int classesCount;
}

class GymMembershipPlan {
  const GymMembershipPlan({
    required this.id,
    required this.providerId,
    required this.nameAr,
    required this.durationDays,
    required this.price,
    this.branchId,
    this.nameEn,
    this.descriptionAr,
    this.descriptionEn,
    this.sessionsCount,
    this.includesClasses = false,
    this.includesPersonalTraining = false,
    this.isActive = true,
    this.branch,
  });

  final int id;
  final int providerId;
  final int? branchId;
  final String nameAr;
  final String? nameEn;
  final String? descriptionAr;
  final String? descriptionEn;
  final int durationDays;
  final String price;
  final int? sessionsCount;
  final bool includesClasses;
  final bool includesPersonalTraining;
  final bool isActive;
  final FitnessBranchSummary? branch;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    return nameAr;
  }

  String? description(bool isArabic) {
    if (!isArabic && descriptionEn?.trim().isNotEmpty == true) {
      return descriptionEn!.trim();
    }
    return descriptionAr?.trim().isEmpty == true ? null : descriptionAr;
  }
}

class GymClass {
  const GymClass({
    required this.id,
    required this.providerId,
    required this.nameAr,
    this.branchId,
    this.coachProviderId,
    this.nameEn,
    this.descriptionAr,
    this.descriptionEn,
    this.startsAt,
    this.endsAt,
    this.capacity,
    this.price,
    this.isActive = true,
    this.branch,
    this.coach,
  });

  final int id;
  final int providerId;
  final int? branchId;
  final int? coachProviderId;
  final String nameAr;
  final String? nameEn;
  final String? descriptionAr;
  final String? descriptionEn;
  final DateTime? startsAt;
  final DateTime? endsAt;
  final int? capacity;
  final String? price;
  final bool isActive;
  final FitnessBranchSummary? branch;
  final FitnessProviderSummary? coach;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    return nameAr;
  }
}

enum GymBookingStatus {
  pendingPayment,
  pendingPaymentReview,
  paid,
  confirmed,
  active,
  completed,
  cancelledByUser,
  cancelledByProvider,
  rejected,
  unknown;

  static GymBookingStatus fromWire(String? value) {
    return switch (value) {
      'pending_payment' => GymBookingStatus.pendingPayment,
      'pending_payment_review' => GymBookingStatus.pendingPaymentReview,
      'paid' => GymBookingStatus.paid,
      'confirmed' => GymBookingStatus.confirmed,
      'active' => GymBookingStatus.active,
      'completed' => GymBookingStatus.completed,
      'cancelled_by_user' => GymBookingStatus.cancelledByUser,
      'cancelled_by_provider' => GymBookingStatus.cancelledByProvider,
      'rejected' => GymBookingStatus.rejected,
      _ => GymBookingStatus.unknown,
    };
  }

  String friendlyLabel(bool isArabic) {
    if (!isArabic) {
      return switch (this) {
        GymBookingStatus.pendingPayment => 'Awaiting payment',
        GymBookingStatus.pendingPaymentReview => 'Payment under review',
        GymBookingStatus.paid => 'Paid',
        GymBookingStatus.confirmed => 'Confirmed',
        GymBookingStatus.active => 'Active',
        GymBookingStatus.completed => 'Completed',
        GymBookingStatus.cancelledByUser => 'Cancelled by you',
        GymBookingStatus.cancelledByProvider => 'Cancelled by gym',
        GymBookingStatus.rejected => 'Rejected',
        GymBookingStatus.unknown => 'Unknown',
      };
    }
    return switch (this) {
      GymBookingStatus.pendingPayment => 'في انتظار الدفع',
      GymBookingStatus.pendingPaymentReview => 'جاري مراجعة الدفع',
      GymBookingStatus.paid => 'تم الدفع',
      GymBookingStatus.confirmed => 'مؤكد',
      GymBookingStatus.active => 'نشط',
      GymBookingStatus.completed => 'مكتمل',
      GymBookingStatus.cancelledByUser => 'ملغي بواسطتك',
      GymBookingStatus.cancelledByProvider => 'ملغي من الجيم',
      GymBookingStatus.rejected => 'مرفوض',
      GymBookingStatus.unknown => 'حالة غير معروفة',
    };
  }

  bool get canPay {
    return this == GymBookingStatus.pendingPayment ||
        this == GymBookingStatus.pendingPaymentReview ||
        this == GymBookingStatus.rejected;
  }
}

class GymBooking {
  const GymBooking({
    required this.id,
    required this.status,
    this.bookingNumber,
    this.providerId,
    this.branchId,
    this.membershipPlanId,
    this.gymClassId,
    this.totalAmount,
    this.currency = 'EGP',
    this.paymentId,
    this.startsAt,
    this.endsAt,
    this.notes,
    this.provider,
    this.branch,
    this.membershipPlan,
    this.gymClass,
  });

  final int id;
  final String? bookingNumber;
  final int? providerId;
  final int? branchId;
  final int? membershipPlanId;
  final int? gymClassId;
  final GymBookingStatus status;
  final String? totalAmount;
  final String currency;
  final int? paymentId;
  final DateTime? startsAt;
  final DateTime? endsAt;
  final String? notes;
  final FitnessProviderSummary? provider;
  final FitnessBranchSummary? branch;
  final GymMembershipPlan? membershipPlan;
  final GymClass? gymClass;

  bool get canContinuePayment => paymentId != null && status.canPay;

  String summary(bool isArabic) {
    if (membershipPlan != null) return membershipPlan!.name(isArabic);
    if (gymClass != null) return gymClass!.name(isArabic);
    return isArabic ? 'حجز جيم' : 'Gym booking';
  }
}

class Coach extends FitnessProviderSummary {
  const Coach({
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
    this.coachType,
    this.experienceYears,
    this.sessionPrice,
    this.monthlyFollowupPrice,
    this.onlineCoachingEnabled = false,
    this.gymVisitEnabled = false,
    this.homeTrainingEnabled = false,
    this.certificationsSummary,
    this.sessionTypesCount = 0,
    this.availabilityCount = 0,
    this.packagesCount = 0,
  });

  final String? coachType;
  final int? experienceYears;
  final String? sessionPrice;
  final String? monthlyFollowupPrice;
  final bool onlineCoachingEnabled;
  final bool gymVisitEnabled;
  final bool homeTrainingEnabled;
  final String? certificationsSummary;
  final int sessionTypesCount;
  final int availabilityCount;
  final int packagesCount;
}

class CoachSessionType {
  const CoachSessionType({
    required this.id,
    required this.providerId,
    required this.nameAr,
    required this.durationMinutes,
    required this.price,
    required this.sessionMode,
    this.nameEn,
    this.descriptionAr,
    this.descriptionEn,
    this.isActive = true,
  });

  final int id;
  final int providerId;
  final String nameAr;
  final String? nameEn;
  final String? descriptionAr;
  final String? descriptionEn;
  final int durationMinutes;
  final String price;
  final String sessionMode;
  final bool isActive;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    return nameAr;
  }
}

class CoachAvailabilitySlot {
  const CoachAvailabilitySlot({
    required this.id,
    required this.providerId,
    required this.status,
    this.startsAt,
    this.endsAt,
  });

  final int id;
  final int providerId;
  final DateTime? startsAt;
  final DateTime? endsAt;
  final String status;

  bool get isAvailable => status == 'available';
}

class CoachPackage {
  const CoachPackage({
    required this.id,
    required this.providerId,
    required this.nameAr,
    required this.sessionsCount,
    required this.price,
    this.nameEn,
    this.descriptionAr,
    this.descriptionEn,
    this.durationDays,
    this.isActive = true,
  });

  final int id;
  final int providerId;
  final String nameAr;
  final String? nameEn;
  final String? descriptionAr;
  final String? descriptionEn;
  final int sessionsCount;
  final int? durationDays;
  final String price;
  final bool isActive;

  String name(bool isArabic) {
    if (!isArabic && nameEn?.trim().isNotEmpty == true) return nameEn!.trim();
    return nameAr;
  }
}

enum CoachBookingStatus {
  pendingPayment,
  pendingPaymentReview,
  paid,
  confirmed,
  inProgress,
  completed,
  cancelledByUser,
  cancelledByCoach,
  rejected,
  unknown;

  static CoachBookingStatus fromWire(String? value) {
    return switch (value) {
      'pending_payment' => CoachBookingStatus.pendingPayment,
      'pending_payment_review' => CoachBookingStatus.pendingPaymentReview,
      'paid' => CoachBookingStatus.paid,
      'confirmed' => CoachBookingStatus.confirmed,
      'in_progress' => CoachBookingStatus.inProgress,
      'completed' => CoachBookingStatus.completed,
      'cancelled_by_user' => CoachBookingStatus.cancelledByUser,
      'cancelled_by_coach' => CoachBookingStatus.cancelledByCoach,
      'rejected' => CoachBookingStatus.rejected,
      _ => CoachBookingStatus.unknown,
    };
  }

  String friendlyLabel(bool isArabic) {
    if (!isArabic) {
      return switch (this) {
        CoachBookingStatus.pendingPayment => 'Awaiting payment',
        CoachBookingStatus.pendingPaymentReview => 'Payment under review',
        CoachBookingStatus.paid => 'Paid',
        CoachBookingStatus.confirmed => 'Confirmed',
        CoachBookingStatus.inProgress => 'In progress',
        CoachBookingStatus.completed => 'Completed',
        CoachBookingStatus.cancelledByUser => 'Cancelled by you',
        CoachBookingStatus.cancelledByCoach => 'Cancelled by coach',
        CoachBookingStatus.rejected => 'Rejected',
        CoachBookingStatus.unknown => 'Unknown',
      };
    }
    return switch (this) {
      CoachBookingStatus.pendingPayment => 'في انتظار الدفع',
      CoachBookingStatus.pendingPaymentReview => 'جاري مراجعة الدفع',
      CoachBookingStatus.paid => 'تم الدفع',
      CoachBookingStatus.confirmed => 'مؤكد',
      CoachBookingStatus.inProgress => 'قيد التنفيذ',
      CoachBookingStatus.completed => 'مكتمل',
      CoachBookingStatus.cancelledByUser => 'ملغي بواسطتك',
      CoachBookingStatus.cancelledByCoach => 'ملغي من الكوتش',
      CoachBookingStatus.rejected => 'مرفوض',
      CoachBookingStatus.unknown => 'حالة غير معروفة',
    };
  }

  bool get canPay {
    return this == CoachBookingStatus.pendingPayment ||
        this == CoachBookingStatus.pendingPaymentReview ||
        this == CoachBookingStatus.rejected;
  }
}

class CoachBooking {
  const CoachBooking({
    required this.id,
    required this.status,
    this.bookingNumber,
    this.coachProviderId,
    this.sessionTypeId,
    this.availabilitySlotId,
    this.totalAmount,
    this.currency = 'EGP',
    this.paymentId,
    this.patientGoal,
    this.coachProvider,
    this.sessionType,
    this.availabilitySlot,
  });

  final int id;
  final String? bookingNumber;
  final int? coachProviderId;
  final int? sessionTypeId;
  final int? availabilitySlotId;
  final CoachBookingStatus status;
  final String? totalAmount;
  final String currency;
  final int? paymentId;
  final String? patientGoal;
  final FitnessProviderSummary? coachProvider;
  final CoachSessionType? sessionType;
  final CoachAvailabilitySlot? availabilitySlot;

  bool get canContinuePayment => paymentId != null && status.canPay;

  String summary(bool isArabic) {
    if (sessionType != null) return sessionType!.name(isArabic);
    return isArabic ? 'حجز كوتش' : 'Coach booking';
  }
}
