class ApiEndpoints {
  const ApiEndpoints._();

  static const login = '/auth/login';
  static const register = '/auth/register';
  static const logout = '/auth/logout';
  static const me = '/me';
  static const workspaces = '/me/workspaces';
  static const doctors = '/doctors';
  static const appointments = '/appointments';
  static const paymentMethods = '/payment-methods';
  static const pharmacies = '/pharmacies';
  static const pharmacyPrescriptions = '/pharmacy/prescriptions';
  static const pharmacyOrders = '/pharmacy/orders';
  static const labs = '/labs';
  static const radiologyScanCategories = '/radiology/scan-categories';
  static const radiologyScans = '/radiology/scans';
  static const radiologyOrders = '/radiology/orders';
  static const hospitals = '/hospitals';
  static const gyms = '/gyms';
  static const gymBookings = '/gym/bookings';
  static const coaches = '/coaches';
  static const coachBookings = '/coach/bookings';
  static const labOrders = '/lab/orders';
  static const healthProfile = '/health/profile';
  static const healthVitals = '/health/vitals';
  static const healthLatestVitals = '/health/vitals/latest';
  static const healthSummary = '/health/summary';
  static const healthVitalTrends = '/health/vitals/trends';
  static const medicationReminders = '/medications/reminders';
  static const medicationLogs = '/medications/logs';
  static const medicationToday = '/medications/today';
  static const medicationUpcoming = '/medications/upcoming';
  static const medicationAdherence = '/medications/adherence';
  static const medicationRefills = '/medications/refills';
  static const carePlans = '/care-plans';
  static const carePlansSummary = '/care-plans/summary';
  static const notifications = '/notifications';
  static const notificationsUnreadCount = '/notifications/unread-count';
  static const notificationsReadAll = '/notifications/read-all';
  static const notificationTokens = '/notification-tokens';
  static const notificationPreferences = '/notification-preferences';
  static const aiConversations = '/ai/conversations';
  static const aiAsk = '/ai/ask';
  static const aiContextPreview = '/ai/context-preview';

  static String doctor(int id) => '/doctors/$id';

  static String doctorSlots(int doctorId) => '/doctors/$doctorId/slots';

  static String appointment(int id) => '/appointments/$id';

  static String cancelAppointment(int id) => '/appointments/$id/cancel';

  static String paymentStatus(int paymentId) => '/payments/$paymentId/status';

  static String manualPaymentSelect(int paymentId) =>
      '/payments/$paymentId/manual/select';

  static String paymentProofs(int paymentId) => '/payments/$paymentId/proofs';

  static String paymobCreateSession(int paymentId) =>
      '/payments/$paymentId/paymob/create-session';

  static String pharmacy(int id) => '/pharmacies/$id';

  static String pharmacyProducts(int pharmacyId) =>
      '/pharmacies/$pharmacyId/products';

  static String pharmacyOrder(int id) => '/pharmacy/orders/$id';

  static String pharmacyOrderPay(int id) => '/pharmacy/orders/$id/pay';

  static String lab(int id) => '/labs/$id';

  static String labTests(int labId) => '/labs/$labId/tests';

  static String labPackages(int labId) => '/labs/$labId/packages';

  static String labOrder(int id) => '/lab/orders/$id';

  static String labOrderPay(int id) => '/lab/orders/$id/pay';

  static String labResultDownload(int id) => '/lab/results/$id/download';

  static String radiologyOrder(int id) => '/radiology/orders/$id';

  static String radiologyOrderCancel(int id) => '/radiology/orders/$id/cancel';

  static String radiologyOrderResults(int id) =>
      '/radiology/orders/$id/results';

  static String radiologyResultDownload(int id) =>
      '/radiology/results/$id/download';

  static String hospital(int id) => '/hospitals/$id';

  static String hospitalDepartments(int hospitalId) =>
      '/hospitals/$hospitalId/departments';

  static String hospitalDoctors(int hospitalId) =>
      '/hospitals/$hospitalId/doctors';

  static String hospitalDepartmentDoctors(int hospitalId, int departmentId) =>
      '/hospitals/$hospitalId/departments/$departmentId/doctors';

  static String gym(int id) => '/gyms/$id';

  static String gymMembershipPlans(int gymId) =>
      '/gyms/$gymId/membership-plans';

  static String gymClasses(int gymId) => '/gyms/$gymId/classes';

  static String gymBooking(int id) => '/gym/bookings/$id';

  static String gymBookingCancel(int id) => '/gym/bookings/$id/cancel';

  static String coach(int id) => '/coaches/$id';

  static String coachSessionTypes(int coachId) =>
      '/coaches/$coachId/session-types';

  static String coachAvailability(int coachId) =>
      '/coaches/$coachId/availability';

  static String coachPackages(int coachId) => '/coaches/$coachId/packages';

  static String coachBooking(int id) => '/coach/bookings/$id';

  static String coachBookingCancel(int id) => '/coach/bookings/$id/cancel';

  static String providerWorkspaceDashboard(int providerId) =>
      '/provider/workspace/$providerId/dashboard';

  static String medicationReminder(int id) => '/medications/reminders/$id';

  static String medicationReminderPause(int id) =>
      '/medications/reminders/$id/pause';

  static String medicationReminderResume(int id) =>
      '/medications/reminders/$id/resume';

  static String medicationReminderCancel(int id) =>
      '/medications/reminders/$id/cancel';

  static String medicationReminderTimes(int id) =>
      '/medications/reminders/$id/times';

  static String medicationReminderTime(int reminderId, int timeId) =>
      '/medications/reminders/$reminderId/times/$timeId';

  static String medicationReminderLogs(int id) =>
      '/medications/reminders/$id/logs';

  static String medicationLog(int id) => '/medications/logs/$id';

  static String medicationTaken(int id) => '/medications/reminders/$id/taken';

  static String medicationSkipped(int id) =>
      '/medications/reminders/$id/skipped';

  static String medicationReminderSchedule(int id) =>
      '/medications/reminders/$id/schedule';

  static String medicationRefillDone(int id) =>
      '/medications/reminders/$id/refill-done';

  static String medicationRefillSkipped(int id) =>
      '/medications/reminders/$id/refill-skipped';

  static String carePlan(int id) => '/care-plans/$id';

  static String carePlanActivate(int id) => '/care-plans/$id/activate';

  static String carePlanPause(int id) => '/care-plans/$id/pause';

  static String carePlanResume(int id) => '/care-plans/$id/resume';

  static String carePlanComplete(int id) => '/care-plans/$id/complete';

  static String carePlanCancel(int id) => '/care-plans/$id/cancel';

  static String carePlanDays(int id) => '/care-plans/$id/days';

  static String carePlanDay(int planId, int dayId) =>
      '/care-plans/$planId/days/$dayId';

  static String carePlanMeals(int id) => '/care-plans/$id/meals';

  static String carePlanMeal(int planId, int mealId) =>
      '/care-plans/$planId/meals/$mealId';

  static String carePlanFoods(int id) => '/care-plans/$id/foods';

  static String carePlanFood(int planId, int foodId) =>
      '/care-plans/$planId/foods/$foodId';

  static String carePlanInstructions(int id) => '/care-plans/$id/instructions';

  static String carePlanInstruction(int planId, int instructionId) =>
      '/care-plans/$planId/instructions/$instructionId';

  static String carePlanCheckins(int id) => '/care-plans/$id/checkins';

  static String carePlanCheckin(int planId, int checkinId) =>
      '/care-plans/$planId/checkins/$checkinId';

  static String carePlanMealLogs(int id) => '/care-plans/$id/meal-logs';

  static String carePlanMealLog(int planId, int logId) =>
      '/care-plans/$planId/meal-logs/$logId';

  static String carePlanProgress(int id) => '/care-plans/$id/progress';

  static String notification(int id) => '/notifications/$id';

  static String notificationRead(int id) => '/notifications/$id/read';

  static String notificationToken(int id) => '/notification-tokens/$id';

  static String aiConversation(int id) => '/ai/conversations/$id';

  static String aiConversationMessages(int id) =>
      '/ai/conversations/$id/messages';

  static String aiToggleContext(int id) =>
      '/ai/conversations/$id/toggle-context';
}
