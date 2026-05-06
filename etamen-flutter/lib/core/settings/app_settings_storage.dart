import 'package:flutter_secure_storage/flutter_secure_storage.dart';

abstract class AppSettingsStorage {
  Future<String?> readLocaleCode();

  Future<void> saveLocaleCode(String localeCode);
}

class SecureAppSettingsStorage implements AppSettingsStorage {
  SecureAppSettingsStorage({FlutterSecureStorage? storage})
    : _storage = storage ?? const FlutterSecureStorage();

  static const _localeKey = 'etamen.locale_code';

  final FlutterSecureStorage _storage;

  @override
  Future<String?> readLocaleCode() {
    return _storage.read(key: _localeKey);
  }

  @override
  Future<void> saveLocaleCode(String localeCode) {
    return _storage.write(key: _localeKey, value: localeCode);
  }
}
