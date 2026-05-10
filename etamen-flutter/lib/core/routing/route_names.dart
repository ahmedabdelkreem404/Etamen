class RouteNames {
  const RouteNames._();

  static const splash = '/splash';
  static const login = '/login';
  static const register = '/register';
  static const home = '/home';
  static const doctors = '/doctors';
  static const appointments = '/appointments';
  static const pharmacies = '/pharmacies';
  static const pharmacyCart = '/pharmacy/cart';
  static const pharmacyOrders = '/pharmacy/orders';
  static const pharmacyPrescriptionUpload = '/pharmacy/prescription-upload';
  static const labs = '/labs';
  static const radiology = '/radiology';
  static const radiologyOrderBuilder = '/radiology/order-builder';
  static const radiologyOrders = '/radiology/orders';
  static const hospitals = '/hospitals';
  static const gyms = '/gyms';
  static const gymBookings = '/gym/bookings';
  static const coaches = '/coaches';
  static const coachBookings = '/coach/bookings';
  static const labCart = '/labs/cart';
  static const labOrders = '/lab-orders';
  static const health = '/health';
  static const healthProfile = '/health/profile';
  static const editHealthProfile = '/health/profile/edit';
  static const healthVitals = '/health/vitals';
  static const medications = '/medications';
  static const medicationReminders = '/medications/reminders';
  static const createMedicationReminder = '/medications/reminders/create';
  static const todayMedications = '/medications/today';
  static const medicationAdherence = '/medications/adherence';
  static const carePlans = '/care-plans';
  static const ai = '/ai';
  static const notifications = '/notifications';
  static const notificationPreferences = '/notifications/preferences';
  static const account = '/account';
  static const accountSettings = '/account/settings';
  static const languageSettings = '/account/language';
  static const legalPrivacy = '/legal/privacy';
  static const legalTerms = '/legal/terms';
  static const legalMedicalDisclaimer = '/legal/medical-disclaimer';
  static const legalAiDisclaimer = '/legal/ai-disclaimer';
  static const legalRefundPolicy = '/legal/refund-policy';
  static const support = '/support';
  static const createSupportTicket = '/support/create';
  static const refunds = '/support/refunds';
  static const createRefund = '/support/refunds/create';
  static const disputes = '/support/disputes';
  static const createDispute = '/support/disputes/create';
  static const about = '/about';
  static const platformAdminDashboard = '/workspace/platform-admin';
  static const adminPayments = '/workspace/platform-admin/payment-reviews';
  static const adminProviders = '/workspace/platform-admin/provider-approvals';
  static const adminSupportTickets =
      '/workspace/platform-admin/support-tickets';
  static const adminRefunds = '/workspace/platform-admin/refunds';
  static const adminDisputes = '/workspace/platform-admin/disputes';
  static const adminAuditLog = '/workspace/platform-admin/audit-log';

  static String adminOperationDetails(String section, int id) =>
      '/workspace/platform-admin/$section/$id';

  static String supportTicketDetails(int id) => '/support/tickets/$id';

  static String refundDetails(int id) => '/support/refunds/$id';

  static String disputeDetails(int id) => '/support/disputes/$id';

  static String providerDashboard(int providerId) =>
      '/workspace/provider/$providerId';

  static String providerOperation(int providerId, String section) {
    final suffix = _query({'section': _encodeSection(section)});
    return '/workspace/provider/$providerId/operations$suffix';
  }

  static String providerOperationDetails(
    int providerId,
    String section,
    int itemId,
  ) {
    final suffix = _query({'section': _encodeSection(section)});
    return '/workspace/provider/$providerId/operations/$itemId$suffix';
  }

  static String doctorProfile(
    int id, {
    int? hospitalId,
    int? departmentId,
    int? hospitalDoctorId,
    String? hospitalName,
    String? departmentName,
  }) {
    final suffix = _query({
      'hospitalId': hospitalId,
      'departmentId': departmentId,
      'hospitalDoctorId': hospitalDoctorId,
      'hospitalName': hospitalName,
      'departmentName': departmentName,
    });
    return '/doctors/$id$suffix';
  }

  static String doctorBooking(
    int id, {
    int? hospitalId,
    int? departmentId,
    int? hospitalDoctorId,
    String? hospitalName,
    String? departmentName,
  }) {
    final suffix = _query({
      'hospitalId': hospitalId,
      'departmentId': departmentId,
      'hospitalDoctorId': hospitalDoctorId,
      'hospitalName': hospitalName,
      'departmentName': departmentName,
    });
    return '/doctors/$id/booking$suffix';
  }

  static String appointmentResult(int id) => '/appointments/$id/result';

  static String appointmentDetails(int id) => '/appointments/$id';

  static String pharmacyProducts(int id) => '/pharmacies/$id/products';

  static String pharmacyOrderDetails(int id) => '/pharmacy/orders/$id';

  static String labTests(int id) => '/labs/$id/tests';

  static String labOrderDetails(int id) => '/lab-orders/$id';

  static String radiologyOrderDetails(int id) => '/radiology/orders/$id';

  static String hospitalDetails(int id) => '/hospitals/$id';

  static String hospitalDepartmentDoctors(int hospitalId, int departmentId) =>
      '/hospitals/$hospitalId/departments/$departmentId/doctors';

  static String gymDetails(int id) => '/gyms/$id';

  static String gymBookingDetails(int id) => '/gym/bookings/$id';

  static String coachDetails(int id) => '/coaches/$id';

  static String coachBookingDetails(int id) => '/coach/bookings/$id';

  static String addVital([dynamic type]) {
    if (type == null) return '/health/vitals/add';
    final value = type is String ? type : type.wireValue.toString();
    return '/health/vitals/add/$value';
  }

  static String medicationReminderDetails(int id) =>
      '/medications/reminders/$id';

  static String carePlanDetails(int id) => '/care-plans/$id';

  static String carePlanDay(int planId, int dayId) =>
      '/care-plans/$planId/day/$dayId';

  static String carePlanCheckin(int id) => '/care-plans/$id/checkin';

  static String carePlanMealLog(int id) => '/care-plans/$id/meal-log';

  static String carePlanProgress(int id) => '/care-plans/$id/progress';

  static String notificationDetails(int id) => '/notifications/$id';

  static String aiConversation(int id) => '/ai/conversations/$id';

  static const aiContextPreview = '/ai/context-preview';

  static String payment(
    int id, {
    int? appointmentId,
    int? pharmacyOrderId,
    int? labOrderId,
    int? radiologyOrderId,
    int? gymBookingId,
    int? coachBookingId,
  }) {
    final suffix = _query({
      'appointmentId': appointmentId,
      'pharmacyOrderId': pharmacyOrderId,
      'labOrderId': labOrderId,
      'radiologyOrderId': radiologyOrderId,
      'gymBookingId': gymBookingId,
      'coachBookingId': coachBookingId,
    });
    return '/payments/$id$suffix';
  }

  static String manualPayment(
    int id, {
    required int methodId,
    int? appointmentId,
    int? pharmacyOrderId,
    int? labOrderId,
    int? radiologyOrderId,
    int? gymBookingId,
    int? coachBookingId,
  }) {
    final suffix = _query({
      'methodId': methodId,
      'appointmentId': appointmentId,
      'pharmacyOrderId': pharmacyOrderId,
      'labOrderId': labOrderId,
      'radiologyOrderId': radiologyOrderId,
      'gymBookingId': gymBookingId,
      'coachBookingId': coachBookingId,
    });
    return '/payments/$id/manual$suffix';
  }

  static String paymentStatus(
    int id, {
    int? appointmentId,
    int? pharmacyOrderId,
    int? labOrderId,
    int? radiologyOrderId,
    int? gymBookingId,
    int? coachBookingId,
  }) {
    final suffix = _query({
      'appointmentId': appointmentId,
      'pharmacyOrderId': pharmacyOrderId,
      'labOrderId': labOrderId,
      'radiologyOrderId': radiologyOrderId,
      'gymBookingId': gymBookingId,
      'coachBookingId': coachBookingId,
    });
    return '/payments/$id/status$suffix';
  }

  static String paymobCheckout(
    int id, {
    int? appointmentId,
    int? pharmacyOrderId,
    int? labOrderId,
    int? radiologyOrderId,
    int? gymBookingId,
    int? coachBookingId,
  }) {
    final suffix = _query({
      'appointmentId': appointmentId,
      'pharmacyOrderId': pharmacyOrderId,
      'labOrderId': labOrderId,
      'radiologyOrderId': radiologyOrderId,
      'gymBookingId': gymBookingId,
      'coachBookingId': coachBookingId,
    });
    return '/payments/$id/paymob$suffix';
  }

  static String _query(Map<String, Object?> values) {
    final query = values.entries
        .where((entry) => entry.value != null)
        .fold<Map<String, String>>({}, (map, entry) {
          map[entry.key] = entry.value.toString();
          return map;
        });
    if (query.isEmpty) return '';
    return '?${Uri(queryParameters: query).query}';
  }

  static String _encodeSection(String section) => section.replaceAll('/', '__');
}
