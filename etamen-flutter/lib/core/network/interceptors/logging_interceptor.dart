import 'package:dio/dio.dart';

class LoggingInterceptor extends Interceptor {
  @override
  void onRequest(RequestOptions options, RequestInterceptorHandler handler) {
    assert(() {
      // Keep intentionally quiet; this hook is here for local debugging only.
      return true;
    }());
    handler.next(options);
  }
}
