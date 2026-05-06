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
  static const labCart = '/labs/cart';
  static const labOrders = '/lab-orders';
  static const account = '/account';

  static String doctorProfile(int id) => '/doctors/$id';

  static String doctorBooking(int id) => '/doctors/$id/booking';

  static String appointmentResult(int id) => '/appointments/$id/result';

  static String appointmentDetails(int id) => '/appointments/$id';

  static String pharmacyProducts(int id) => '/pharmacies/$id/products';

  static String pharmacyOrderDetails(int id) => '/pharmacy/orders/$id';

  static String labTests(int id) => '/labs/$id/tests';

  static String labOrderDetails(int id) => '/lab-orders/$id';

  static String payment(
    int id, {
    int? appointmentId,
    int? pharmacyOrderId,
    int? labOrderId,
  }) {
    final suffix = _query({
      'appointmentId': appointmentId,
      'pharmacyOrderId': pharmacyOrderId,
      'labOrderId': labOrderId,
    });
    return '/payments/$id$suffix';
  }

  static String manualPayment(
    int id, {
    required int methodId,
    int? appointmentId,
    int? pharmacyOrderId,
    int? labOrderId,
  }) {
    final suffix = _query({
      'methodId': methodId,
      'appointmentId': appointmentId,
      'pharmacyOrderId': pharmacyOrderId,
      'labOrderId': labOrderId,
    });
    return '/payments/$id/manual$suffix';
  }

  static String paymentStatus(
    int id, {
    int? appointmentId,
    int? pharmacyOrderId,
    int? labOrderId,
  }) {
    final suffix = _query({
      'appointmentId': appointmentId,
      'pharmacyOrderId': pharmacyOrderId,
      'labOrderId': labOrderId,
    });
    return '/payments/$id/status$suffix';
  }

  static String paymobCheckout(
    int id, {
    int? appointmentId,
    int? pharmacyOrderId,
    int? labOrderId,
  }) {
    final suffix = _query({
      'appointmentId': appointmentId,
      'pharmacyOrderId': pharmacyOrderId,
      'labOrderId': labOrderId,
    });
    return '/payments/$id/paymob$suffix';
  }

  static String _query(Map<String, Object?> values) {
    final entries = values.entries
        .where((entry) => entry.value != null)
        .map((entry) => '${entry.key}=${entry.value}')
        .toList();
    return entries.isEmpty ? '' : '?${entries.join('&')}';
  }
}
