import 'package:dio/dio.dart';
import 'package:etamen_app/core/storage/token_storage.dart';

class AuthInterceptor extends Interceptor {
  AuthInterceptor({required this.tokenStorage, this.onUnauthenticated});

  final TokenStorage tokenStorage;
  final void Function()? onUnauthenticated;

  @override
  void onRequest(
    RequestOptions options,
    RequestInterceptorHandler handler,
  ) async {
    final token = await tokenStorage.readToken();
    if (token != null && token.isNotEmpty) {
      options.headers['Authorization'] = 'Bearer $token';
    }
    handler.next(options);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) async {
    if (err.response?.statusCode == 401) {
      await tokenStorage.clearToken();
      onUnauthenticated?.call();
    }
    handler.next(err);
  }
}
