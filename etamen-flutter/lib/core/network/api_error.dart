enum ApiErrorType {
  network,
  unauthenticated,
  forbidden,
  validation,
  rateLimited,
  server,
  unknown,
}

class ApiError {
  const ApiError({
    required this.message,
    required this.type,
    this.statusCode,
    this.validationErrors = const {},
  });

  final String message;
  final int? statusCode;
  final ApiErrorType type;
  final Map<String, List<String>> validationErrors;

  bool get hasValidationErrors => validationErrors.isNotEmpty;
}
