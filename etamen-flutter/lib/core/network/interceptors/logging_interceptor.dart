import 'package:dio/dio.dart';
import 'package:etamen_app/core/config/app_config.dart';
import 'package:flutter/foundation.dart';

class LoggingInterceptor extends Interceptor {
  static bool get _enabled =>
      kDebugMode && AppConfig.environment.toLowerCase() == 'staging';

  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    if (_enabled) {
      _log(
        'request ${options.method} ${_safeUri(options)} '
        'connect=${options.connectTimeout?.inSeconds}s '
        'receive=${options.receiveTimeout?.inSeconds}s',
      );
    }
    handler.next(options);
  }

  @override
  void onResponse(
    Response<dynamic> response,
    ResponseInterceptorHandler handler,
  ) {
    if (_enabled) {
      _log(
        'response ${response.statusCode} '
        '${response.requestOptions.method} ${_safeUri(response.requestOptions)} '
        '${_safeBodySummary(response.data)}',
      );
    }
    handler.next(response);
  }

  @override
  void onError(DioException err, ErrorInterceptorHandler handler) {
    if (_enabled) {
      final response = err.response;
      _log(
        'error type=${err.type.name} status=${response?.statusCode ?? '-'} '
        '${err.requestOptions.method} ${_safeUri(err.requestOptions)} '
        'message=${err.message ?? '-'} '
        '${_safeBodySummary(response?.data)}',
      );
    }
    handler.next(err);
  }

  static String _safeUri(RequestOptions options) {
    final uri = options.uri.replace(query: '');
    return uri.toString();
  }

  static String _safeBodySummary(Object? body) {
    if (body is Map<String, dynamic>) {
      final message = body['message']?.toString();
      final errors = body['errors'];
      final errorKeys = errors is Map ? errors.keys.join(',') : null;
      return [
        'keys=${body.keys.join(',')}',
        if (message != null && message.isNotEmpty) 'message=$message',
        if (errorKeys != null && errorKeys.isNotEmpty) 'error_keys=$errorKeys',
      ].join(' ');
    }

    if (body == null) return 'body=null';
    return 'body_type=${body.runtimeType}';
  }

  static void _log(String message) {
    debugPrint('[EtamenNetwork] $message');
  }
}
