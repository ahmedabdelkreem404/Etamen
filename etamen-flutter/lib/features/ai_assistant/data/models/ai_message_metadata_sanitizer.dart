class AiMessageMetadataSanitizer {
  const AiMessageMetadataSanitizer();

  static const _blockedFragments = [
    'prompt',
    'system',
    'api_key',
    'apikey',
    'secret',
    'token',
    'provider_response',
    'raw',
    'health_context',
    'payment',
    'wallet',
    'file_path',
    'private',
    'hmac',
    'config',
  ];

  Map<String, dynamic> sanitize(Map<String, dynamic> metadata) {
    final safe = <String, dynamic>{};
    for (final entry in metadata.entries) {
      final key = entry.key.toLowerCase();
      if (_blockedFragments.any(key.contains)) continue;
      final value = entry.value;
      if (value is Map) {
        final nested = sanitize(
          value.map((key, value) => MapEntry(key.toString(), value)),
        );
        if (nested.isNotEmpty) safe[entry.key] = nested;
      } else if (value is List) {
        safe[entry.key] = value
            .where((item) => item is num || item is String || item is bool)
            .toList(growable: false);
      } else if (value == null ||
          value is num ||
          value is String ||
          value is bool) {
        safe[entry.key] = value;
      }
    }
    return safe;
  }
}
