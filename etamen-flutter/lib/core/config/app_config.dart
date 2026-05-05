class AppConfig {
  const AppConfig._();

  static const appName = 'Etamen';

  static const environment = String.fromEnvironment(
    'ETAMEN_ENV',
    defaultValue: 'local',
  );

  static const apiBaseUrl = String.fromEnvironment(
    'ETAMEN_API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );
}
