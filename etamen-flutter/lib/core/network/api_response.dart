class ApiResponse<T> {
  const ApiResponse({
    required this.success,
    required this.message,
    required this.data,
    required this.errors,
  });

  final bool success;
  final String message;
  final T? data;
  final Map<String, List<String>> errors;

  factory ApiResponse.fromJson(
    Map<String, dynamic> json,
    T Function(Object? raw) parser,
  ) {
    return ApiResponse<T>(
      success: json['success'] == true,
      message: (json['message'] ?? '').toString(),
      data: json.containsKey('data') ? parser(json['data']) : null,
      errors: parseErrors(json['errors']),
    );
  }

  static Map<String, List<String>> parseErrors(Object? raw) {
    if (raw is! Map) return {};

    final result = <String, List<String>>{};
    for (final entry in raw.entries) {
      final value = entry.value;
      if (value is List) {
        result[entry.key.toString()] = value
            .map((item) => item.toString())
            .toList(growable: false);
      } else if (value != null) {
        result[entry.key.toString()] = [value.toString()];
      }
    }

    return result;
  }
}
