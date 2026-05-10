Map<String, dynamic>? asFitnessMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }
  return null;
}

Object? unwrapFitnessCollection(Object? raw) {
  if (raw is Map) {
    return raw['data'] ?? raw['items'] ?? raw['bookings'] ?? raw['results'];
  }
  return raw;
}

List<Map<String, dynamic>> fitnessList(Object? raw) {
  final value = unwrapFitnessCollection(raw);
  if (value is! List) return const [];
  return value
      .whereType<Map>()
      .map((item) => item.map((key, value) => MapEntry(key.toString(), value)))
      .toList(growable: false);
}

Map<String, dynamic> unwrapFitnessMap(Object? raw) {
  if (raw is Map<String, dynamic>) {
    if (_looksLikeFitnessEntity(raw)) return raw;
    final nested =
        raw['data'] ??
        raw['gym'] ??
        raw['coach'] ??
        raw['booking'] ??
        raw['gym_booking'] ??
        raw['coach_booking'] ??
        raw['membership_plan'] ??
        raw['gym_class'] ??
        raw['session_type'] ??
        raw['availability_slot'] ??
        raw['package'];
    if (nested is Map<String, dynamic>) return nested;
    if (nested is Map) {
      return nested.map((key, value) => MapEntry(key.toString(), value));
    }
    return raw;
  }
  if (raw is Map) {
    final normalized = raw.map((key, value) => MapEntry(key.toString(), value));
    if (_looksLikeFitnessEntity(normalized)) return normalized;
    return normalized;
  }
  return const {};
}

bool _looksLikeFitnessEntity(Map<String, dynamic> raw) {
  if (!raw.containsKey('id')) return false;
  return raw.containsKey('booking_number') ||
      raw.containsKey('status') ||
      raw.containsKey('name_ar') ||
      raw.containsKey('provider_id') ||
      raw.containsKey('price') ||
      raw.containsKey('starts_at');
}

int? fitnessInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

bool fitnessBool(Object? value, {bool defaultValue = false}) {
  if (value == null) return defaultValue;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final normalized = value.toString().toLowerCase().trim();
  return normalized == '1' || normalized == 'true' || normalized == 'yes';
}

DateTime? fitnessDate(Object? value) {
  if (value == null) return null;
  return DateTime.tryParse(value.toString());
}
