import 'package:dio/dio.dart';
import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_response.dart';

class ErrorMapper {
  const ErrorMapper();

  ApiError fromDio(Object error) {
    if (error is! DioException) {
      return const ApiError(
        message: 'حدث خطأ غير متوقع',
        type: ApiErrorType.unknown,
      );
    }

    final statusCode = error.response?.statusCode;
    final body = error.response?.data;
    final envelope = body is Map<String, dynamic> ? body : null;
    final message = (envelope?['message'] ?? _messageForStatus(statusCode))
        .toString();
    final validationErrors = ApiResponse.parseErrors(envelope?['errors']);

    return ApiError(
      message: message,
      statusCode: statusCode,
      type: _typeForStatus(statusCode, error.type),
      validationErrors: validationErrors,
    );
  }

  ApiError fromEnvelope(Map<String, dynamic> envelope, {int? statusCode}) {
    return ApiError(
      message: (envelope['message'] ?? _messageForStatus(statusCode))
          .toString(),
      statusCode: statusCode,
      type: _typeForStatus(statusCode, DioExceptionType.badResponse),
      validationErrors: ApiResponse.parseErrors(envelope['errors']),
    );
  }

  ApiErrorType _typeForStatus(int? statusCode, DioExceptionType dioType) {
    if (dioType == DioExceptionType.connectionError ||
        dioType == DioExceptionType.connectionTimeout ||
        dioType == DioExceptionType.receiveTimeout ||
        dioType == DioExceptionType.sendTimeout) {
      return ApiErrorType.network;
    }

    if (statusCode == 401) return ApiErrorType.unauthenticated;
    if (statusCode == 403) return ApiErrorType.forbidden;
    if (statusCode == 422) return ApiErrorType.validation;
    if (statusCode == 429) return ApiErrorType.rateLimited;
    if (statusCode != null && statusCode >= 500) return ApiErrorType.server;

    return ApiErrorType.unknown;
  }

  String _messageForStatus(int? statusCode) {
    if (statusCode == 401) return 'انتهت الجلسة، سجل دخول مرة أخرى';
    if (statusCode == 403) return 'ليس لديك صلاحية لتنفيذ هذا الإجراء';
    if (statusCode == 422) return 'راجع البيانات المطلوبة';
    if (statusCode == 429) return 'محاولات كثيرة، حاول بعد قليل';
    if (statusCode != null && statusCode >= 500) return 'حدث خطأ غير متوقع';

    return 'تعذر الاتصال بالسيرفر';
  }
}
