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
}
