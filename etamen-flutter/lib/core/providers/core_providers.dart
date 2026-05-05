import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/storage/secure_storage_service.dart';
import 'package:etamen_app/core/storage/token_storage.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) {
  return SecureTokenStorage();
});

final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(tokenStorage: ref.watch(tokenStorageProvider));
});
