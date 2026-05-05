abstract class TokenStorage {
  Future<String?> readToken();

  Future<void> saveToken(String token);

  Future<void> clearToken();
}
