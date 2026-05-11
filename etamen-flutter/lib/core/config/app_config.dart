class AppConfig {
  const AppConfig._();

  static const appName = 'Etamen';

  static const environment = String.fromEnvironment(
    'ETAMEN_ENV',
    defaultValue: 'unset',
  );

  static bool get isLocalEnvironment => isLocalEnvironmentValue(environment);

  static bool isLocalEnvironmentValue(String? value) {
    return value?.trim().toLowerCase() == 'local';
  }

  static const apiBaseUrl = String.fromEnvironment(
    'ETAMEN_API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );

  static const supportEmail = String.fromEnvironment(
    'ETAMEN_SUPPORT_EMAIL',
    defaultValue: '',
  );

  static const supportPhone = String.fromEnvironment(
    'ETAMEN_SUPPORT_PHONE',
    defaultValue: '',
  );

  static const supportWhatsappUrl = String.fromEnvironment(
    'ETAMEN_SUPPORT_WHATSAPP_URL',
    defaultValue: '',
  );

  static const appVersion = String.fromEnvironment(
    'ETAMEN_APP_VERSION',
    defaultValue: '1.0.0',
  );

  static const buildNumber = String.fromEnvironment(
    'ETAMEN_BUILD_NUMBER',
    defaultValue: '1',
  );
}
