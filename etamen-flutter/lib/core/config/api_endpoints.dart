class ApiEndpoints {
  const ApiEndpoints._();

  static const login = '/auth/login';
  static const register = '/auth/register';
  static const logout = '/auth/logout';
  static const me = '/me';
  static const doctors = '/doctors';
  static const appointments = '/appointments';

  static String doctor(int id) => '/doctors/$id';

  static String doctorSlots(int doctorId) => '/doctors/$doctorId/slots';
}
