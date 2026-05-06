import 'package:dio/dio.dart';
import 'package:etamen_app/core/config/app_config.dart';
import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_response.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/network/error_mapper.dart';
import 'package:etamen_app/core/network/interceptors/auth_interceptor.dart';
import 'package:etamen_app/core/network/interceptors/logging_interceptor.dart';
import 'package:etamen_app/core/storage/token_storage.dart';

class ApiClient {
  ApiClient({
    required TokenStorage tokenStorage,
    Dio? dio,
    ErrorMapper? errorMapper,
    void Function()? onUnauthenticated,
  }) : _dio =
           dio ??
           Dio(
             BaseOptions(
               baseUrl: AppConfig.apiBaseUrl,
               connectTimeout: const Duration(seconds: 15),
               receiveTimeout: const Duration(seconds: 20),
               sendTimeout: const Duration(seconds: 20),
               headers: const {
                 'Accept': 'application/json',
                 'Content-Type': 'application/json',
               },
             ),
           ),
       _errorMapper = errorMapper ?? const ErrorMapper() {
    _dio.interceptors.addAll([
      AuthInterceptor(
        tokenStorage: tokenStorage,
        onUnauthenticated: onUnauthenticated,
      ),
      LoggingInterceptor(),
    ]);
  }

  final Dio _dio;
  final ErrorMapper _errorMapper;

  Dio get rawDio => _dio;

  Future<ApiResult<T>> get<T>(
    String path, {
    Map<String, dynamic>? queryParameters,
    required T Function(Object? raw) parser,
  }) {
    return _request<T>(
      () => _dio.get<Object?>(path, queryParameters: queryParameters),
      parser,
    );
  }

  Future<ApiResult<T>> post<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    required T Function(Object? raw) parser,
  }) {
    return _request<T>(
      () => _dio.post<Object?>(
        path,
        data: data,
        queryParameters: queryParameters,
      ),
      parser,
    );
  }

  Future<ApiResult<T>> put<T>(
    String path, {
    Object? data,
    Map<String, dynamic>? queryParameters,
    required T Function(Object? raw) parser,
  }) {
    return _request<T>(
      () =>
          _dio.put<Object?>(path, data: data, queryParameters: queryParameters),
      parser,
    );
  }

  Future<ApiResult<T>> multipart<T>(
    String path, {
    required FormData formData,
    required T Function(Object? raw) parser,
  }) {
    return _request<T>(() => _dio.post<Object?>(path, data: formData), parser);
  }

  Future<ApiResult<T>> _request<T>(
    Future<Response<Object?>> Function() call,
    T Function(Object? raw) parser,
  ) async {
    try {
      final response = await call();
      final body = response.data;
      if (body is! Map<String, dynamic>) {
        return ApiFailure<T>(
          const ApiError(
            message: 'استجابة غير متوقعة',
            type: ApiErrorType.unknown,
          ),
        );
      }

      final envelope = ApiResponse<T>.fromJson(body, parser);
      if (envelope.success) {
        return ApiSuccess<T>(envelope.data as T, message: envelope.message);
      }

      return ApiFailure<T>(
        _errorMapper.fromEnvelope(body, statusCode: response.statusCode),
      );
    } catch (error) {
      return ApiFailure<T>(_errorMapper.fromDio(error));
    }
  }
}
