class RouteNames {
  const RouteNames._();

  static const splash = '/splash';
  static const login = '/login';
  static const register = '/register';
  static const home = '/home';
  static const doctors = '/doctors';
  static const appointments = '/appointments';
  static const account = '/account';

  static String doctorProfile(int id) => '/doctors/$id';

  static String doctorBooking(int id) => '/doctors/$id/booking';

  static String appointmentResult(int id) => '/appointments/$id/result';

  static String appointmentDetails(int id) => '/appointments/$id';

  static String payment(int id, {int? appointmentId}) {
    final suffix = appointmentId == null ? '' : '?appointmentId=$appointmentId';
    return '/payments/$id$suffix';
  }

  static String manualPayment(
    int id, {
    required int methodId,
    int? appointmentId,
  }) {
    final appointment = appointmentId == null
        ? ''
        : '&appointmentId=$appointmentId';
    return '/payments/$id/manual?methodId=$methodId$appointment';
  }

  static String paymentStatus(int id, {int? appointmentId}) {
    final suffix = appointmentId == null ? '' : '?appointmentId=$appointmentId';
    return '/payments/$id/status$suffix';
  }

  static String paymobCheckout(int id, {int? appointmentId}) {
    final suffix = appointmentId == null ? '' : '?appointmentId=$appointmentId';
    return '/payments/$id/paymob$suffix';
  }
}
