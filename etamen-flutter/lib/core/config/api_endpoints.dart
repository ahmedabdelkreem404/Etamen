class ApiEndpoints {
  const ApiEndpoints._();

  static const login = '/auth/login';
  static const register = '/auth/register';
  static const logout = '/auth/logout';
  static const me = '/me';
  static const doctors = '/doctors';
  static const appointments = '/appointments';
  static const paymentMethods = '/payment-methods';
  static const pharmacies = '/pharmacies';
  static const pharmacyPrescriptions = '/pharmacy/prescriptions';
  static const pharmacyOrders = '/pharmacy/orders';
  static const labs = '/labs';
  static const labOrders = '/lab/orders';
  static const healthProfile = '/health/profile';
  static const healthVitals = '/health/vitals';
  static const healthLatestVitals = '/health/vitals/latest';
  static const healthSummary = '/health/summary';
  static const healthVitalTrends = '/health/vitals/trends';

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
}
