class ProviderOperationSection {
  const ProviderOperationSection({
    required this.section,
    required this.titleAr,
    required this.titleEn,
    required this.iconKey,
    this.detailsEnabled = true,
    this.managePermission,
    this.actions = const [],
  });

  final String section;
  final String titleAr;
  final String titleEn;
  final String iconKey;
  final bool detailsEnabled;
  final String? managePermission;
  final List<ProviderOperationAction> actions;

  String title(bool isArabic) => isArabic ? titleAr : titleEn;
}

class ProviderOperationAction {
  const ProviderOperationAction({
    required this.key,
    required this.labelAr,
    required this.labelEn,
  });

  final String key;
  final String labelAr;
  final String labelEn;

  String label(bool isArabic) => isArabic ? labelAr : labelEn;
}

ProviderOperationSection? operationSectionForQuickAction(
  String providerType,
  String actionKey,
) {
  final section = switch ((providerType, actionKey)) {
    ('doctor', 'appointments') => 'doctor/appointments',
    ('hospital', 'appointments') => 'hospital/appointments',
    ('hospital', 'departments') => 'hospital/departments',
    ('hospital', 'doctors') => 'hospital/doctors',
    ('radiology', 'orders') => 'radiology/orders',
    ('radiology', 'upload_result') => 'radiology/orders',
    ('pharmacy', 'orders') => 'pharmacy/orders',
    ('pharmacy', 'products') => 'pharmacy/products',
    ('pharmacy', 'prescriptions') => 'pharmacy/orders',
    ('lab', 'orders') => 'lab/orders',
    ('lab', 'upload_result') => 'lab/orders',
    ('lab', 'catalog') => 'lab/catalog',
    ('gym', 'bookings') => 'gym/bookings',
    ('gym', 'plans') => 'gym/plans',
    ('gym', 'classes') => 'gym/classes',
    ('fitness_coach', 'bookings') => 'coach/bookings',
    ('fitness_coach', 'availability') => 'coach/availability',
    ('fitness_coach', 'session_types') => 'coach/session-types',
    ('nutrition_coach', 'bookings') => 'coach/bookings',
    ('nutrition_coach', 'availability') => 'coach/availability',
    ('nutrition_coach', 'session_types') => 'coach/session-types',
    _ => null,
  };

  return section == null ? null : providerOperationSection(section);
}

ProviderOperationSection providerOperationSection(String section) {
  return switch (section) {
    'doctor/appointments' => const ProviderOperationSection(
      section: 'doctor/appointments',
      titleAr: 'حجوزات الطبيب',
      titleEn: 'Doctor appointments',
      iconKey: 'appointments',
      managePermission: 'manage_appointments',
      actions: [
        ProviderOperationAction(
          key: 'confirm',
          labelAr: 'قبول الحجز',
          labelEn: 'Accept',
        ),
        ProviderOperationAction(
          key: 'complete',
          labelAr: 'إنهاء الحجز',
          labelEn: 'Complete',
        ),
        ProviderOperationAction(
          key: 'cancel',
          labelAr: 'إلغاء الحجز',
          labelEn: 'Cancel',
        ),
      ],
    ),
    'hospital/appointments' => const ProviderOperationSection(
      section: 'hospital/appointments',
      titleAr: 'حجوزات المستشفى',
      titleEn: 'Hospital appointments',
      iconKey: 'appointments',
    ),
    'hospital/departments' => const ProviderOperationSection(
      section: 'hospital/departments',
      titleAr: 'أقسام المستشفى',
      titleEn: 'Hospital departments',
      iconKey: 'departments',
      detailsEnabled: false,
    ),
    'hospital/doctors' => const ProviderOperationSection(
      section: 'hospital/doctors',
      titleAr: 'أطباء المستشفى',
      titleEn: 'Hospital doctors',
      iconKey: 'doctors',
      detailsEnabled: false,
    ),
    'radiology/orders' => const ProviderOperationSection(
      section: 'radiology/orders',
      titleAr: 'طلبات الأشعة',
      titleEn: 'Radiology orders',
      iconKey: 'orders',
      managePermission: 'manage_radiology_orders',
      actions: [
        ProviderOperationAction(
          key: 'accept',
          labelAr: 'قبول الطلب',
          labelEn: 'Accept',
        ),
        ProviderOperationAction(
          key: 'start',
          labelAr: 'بدء التنفيذ',
          labelEn: 'Start',
        ),
        ProviderOperationAction(
          key: 'result-ready',
          labelAr: 'النتيجة جاهزة',
          labelEn: 'Result ready',
        ),
        ProviderOperationAction(
          key: 'complete',
          labelAr: 'إنهاء الطلب',
          labelEn: 'Complete',
        ),
      ],
    ),
    'pharmacy/orders' => const ProviderOperationSection(
      section: 'pharmacy/orders',
      titleAr: 'طلبات الصيدلية',
      titleEn: 'Pharmacy orders',
      iconKey: 'orders',
    ),
    'pharmacy/products' => const ProviderOperationSection(
      section: 'pharmacy/products',
      titleAr: 'منتجات الصيدلية',
      titleEn: 'Pharmacy products',
      iconKey: 'products',
      detailsEnabled: false,
    ),
    'lab/orders' => const ProviderOperationSection(
      section: 'lab/orders',
      titleAr: 'طلبات المعمل',
      titleEn: 'Lab orders',
      iconKey: 'orders',
    ),
    'lab/catalog' => const ProviderOperationSection(
      section: 'lab/catalog',
      titleAr: 'فهرس المعمل',
      titleEn: 'Lab catalog',
      iconKey: 'catalog',
      detailsEnabled: false,
    ),
    'gym/bookings' => const ProviderOperationSection(
      section: 'gym/bookings',
      titleAr: 'حجوزات الجيم',
      titleEn: 'Gym bookings',
      iconKey: 'bookings',
      managePermission: 'manage_gym_bookings',
      actions: [
        ProviderOperationAction(
          key: 'confirm',
          labelAr: 'تأكيد',
          labelEn: 'Confirm',
        ),
        ProviderOperationAction(
          key: 'activate',
          labelAr: 'تفعيل',
          labelEn: 'Activate',
        ),
        ProviderOperationAction(
          key: 'complete',
          labelAr: 'إنهاء',
          labelEn: 'Complete',
        ),
      ],
    ),
    'gym/plans' => const ProviderOperationSection(
      section: 'gym/plans',
      titleAr: 'اشتراكات الجيم',
      titleEn: 'Gym plans',
      iconKey: 'plans',
      detailsEnabled: false,
    ),
    'gym/classes' => const ProviderOperationSection(
      section: 'gym/classes',
      titleAr: 'حصص الجيم',
      titleEn: 'Gym classes',
      iconKey: 'classes',
      detailsEnabled: false,
    ),
    'coach/bookings' => const ProviderOperationSection(
      section: 'coach/bookings',
      titleAr: 'حجوزات الكوتش',
      titleEn: 'Coach bookings',
      iconKey: 'bookings',
      managePermission: 'manage_coach_bookings',
      actions: [
        ProviderOperationAction(
          key: 'confirm',
          labelAr: 'تأكيد',
          labelEn: 'Confirm',
        ),
        ProviderOperationAction(
          key: 'start',
          labelAr: 'بدء الجلسة',
          labelEn: 'Start',
        ),
        ProviderOperationAction(
          key: 'complete',
          labelAr: 'إنهاء',
          labelEn: 'Complete',
        ),
      ],
    ),
    'coach/availability' => const ProviderOperationSection(
      section: 'coach/availability',
      titleAr: 'مواعيد الكوتش',
      titleEn: 'Coach availability',
      iconKey: 'availability',
      detailsEnabled: false,
    ),
    'coach/session-types' => const ProviderOperationSection(
      section: 'coach/session-types',
      titleAr: 'أنواع الجلسات',
      titleEn: 'Session types',
      iconKey: 'sessions',
      detailsEnabled: false,
    ),
    _ => ProviderOperationSection(
      section: section,
      titleAr: 'تشغيل المزود',
      titleEn: 'Provider operation',
      iconKey: 'default',
      detailsEnabled: false,
    ),
  };
}
