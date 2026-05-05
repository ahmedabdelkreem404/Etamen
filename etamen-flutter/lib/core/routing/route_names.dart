class RouteNames {
  const RouteNames._();

  static const splash = '/splash';
  static const login = '/login';
  static const register = '/register';
  static const home = '/home';
  static const doctors = '/doctors';
  static const account = '/account';

  static String doctorProfile(int id) => '/doctors/$id';

  static String doctorBooking(int id) => '/doctors/$id/booking';

  static String appointmentResult(int id) => '/appointments/$id/result';
}
