Map<String, dynamic>? asRadiologyMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return null;
}

List<Map<String, dynamic>> radiologyList(Object? raw) {
  final value = unwrapRadiologyCollection(raw);
  if (value is! List) return const [];
  return value
      .whereType<Map>()
      .map((item) => item.map((key, value) => MapEntry(key.toString(), value)))
      .toList(growable: false);
}

Object? unwrapRadiologyCollection(Object? raw) {
  if (raw is Map) {
    return raw['data'] ?? raw['items'] ?? raw['orders'] ?? raw['results'];
  }
  return raw;
}

Map<String, dynamic> unwrapRadiologyMap(Object? raw) {
  if (raw is Map<String, dynamic>) {
    final nested =
        raw['data'] ??
        raw['radiology_order'] ??
        raw['order'] ??
        raw['scan'] ??
        raw['category'];
    if (nested is Map<String, dynamic>) return nested;
    if (nested is Map) {
      return nested.map((key, value) => MapEntry(key.toString(), value));
    }
    return raw;
  }
  if (raw is Map) {
    return raw.map((key, value) => MapEntry(key.toString(), value));
  }
  return const {};
}

int? radiologyInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

bool radiologyBool(Object? value, {bool defaultValue = false}) {
  if (value == null) return defaultValue;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final normalized = value.toString().toLowerCase().trim();
  return normalized == '1' || normalized == 'true' || normalized == 'yes';
}
