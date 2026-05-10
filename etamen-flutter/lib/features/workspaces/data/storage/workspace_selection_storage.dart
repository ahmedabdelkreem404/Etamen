import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class WorkspaceSelectionStorage {
  WorkspaceSelectionStorage({FlutterSecureStorage? storage})
    : _storage = storage ?? const FlutterSecureStorage();

  static const _key = 'etamen.selected_workspace';

  final FlutterSecureStorage _storage;

  Future<String?> readSelectedWorkspaceKey() {
    return _storage.read(key: _key);
  }

  Future<void> saveSelectedWorkspaceKey(String key) {
    return _storage.write(key: _key, value: key);
  }

  Future<void> clearSelectedWorkspaceKey() {
    return _storage.delete(key: _key);
  }
}
