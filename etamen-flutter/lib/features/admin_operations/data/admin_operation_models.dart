class AdminDashboard {
  const AdminDashboard({
    required this.counts,
    required this.quickActions,
    required this.recentEvents,
  });

  final Map<String, int> counts;
  final List<AdminQuickAction> quickActions;
  final List<AdminListItem> recentEvents;

  factory AdminDashboard.fromJson(Map<String, dynamic> json) {
    final countKeys = [
      'pending_payment_reviews_count',
      'pending_provider_approvals_count',
      'open_support_tickets_count',
      'open_refund_requests_count',
      'unresolved_disputes_count',
      'today_appointments_count',
      'today_radiology_orders_count',
      'today_gym_bookings_count',
      'today_coach_bookings_count',
    ];

    return AdminDashboard(
      counts: {for (final key in countKeys) key: _int(json[key]) ?? 0},
      quickActions: _list(json['quick_actions'])
          .map((item) => AdminQuickAction.fromJson(_map(item)))
          .toList(growable: false),
      recentEvents: _list(json['recent_events'])
          .map((item) => AdminListItem.fromJson(_map(item)))
          .toList(growable: false),
    );
  }
}

class AdminQuickAction {
  const AdminQuickAction({
    required this.key,
    required this.labelAr,
    required this.labelEn,
  });

  final String key;
  final String labelAr;
  final String labelEn;

  factory AdminQuickAction.fromJson(Map<String, dynamic> json) {
    return AdminQuickAction(
      key: _string(json['key']) ?? '',
      labelAr: _string(json['label_ar']) ?? _string(json['label_en']) ?? '',
      labelEn: _string(json['label_en']) ?? _string(json['label_ar']) ?? '',
    );
  }

  String label(bool isArabic) => isArabic ? labelAr : labelEn;
}

class AdminListResponse {
  const AdminListResponse({required this.items});

  final List<AdminListItem> items;

  factory AdminListResponse.fromJson(Object? raw) {
    final rows = raw is Map<String, dynamic> && raw['data'] is List
        ? _list(raw['data'])
        : _list(raw);
    return AdminListResponse(
      items: rows
          .map((item) => AdminListItem.fromJson(_map(item)))
          .toList(growable: false),
    );
  }
}

class AdminListItem {
  const AdminListItem({required this.raw});

  final Map<String, dynamic> raw;

  factory AdminListItem.fromJson(Map<String, dynamic> json) {
    return AdminListItem(raw: json);
  }

  int get id => _int(raw['id']) ?? 0;

  String get status =>
      _string(raw['status']) ??
      _string(raw['payment_status']) ??
      _string(_map(raw['payable'])['status']) ??
      '';

  String title(bool isArabic) {
    final keys = [
      'ticket_number',
      'refund_number',
      'dispute_number',
      'event',
      'subject',
      'name_ar',
      'name_en',
      'number',
    ];
    for (final key in keys) {
      final value = _string(raw[key]);
      if (value != null) return value;
    }
    final provider = _map(raw['provider']);
    final providerName = isArabic
        ? _string(provider['name_ar']) ?? _string(provider['name_en'])
        : _string(provider['name_en']) ?? _string(provider['name_ar']);
    if (providerName != null) return providerName;
    final payable = _map(raw['payable']);
    final number = _string(payable['number']);
    if (number != null) return number;
    return '#$id';
  }

  String subtitle(bool isArabic) {
    final contextLabel = _adminContextLabel(raw, isArabic);
    final parts = <String>[
      if (contextLabel != null) contextLabel,
      if (status.isNotEmpty) friendlyAdminStatus(status, isArabic),
      if (_string(raw['category']) != null) _string(raw['category'])!,
      if (contextLabel == null && _string(raw['provider_type']) != null)
        _string(raw['provider_type'])!,
      if (_string(raw['created_at']) != null) _string(raw['created_at'])!,
    ];
    final amount = _string(raw['amount']);
    final currency = _string(raw['currency']);
    if (amount != null) {
      parts.insert(0, '$amount ${currency ?? 'EGP'}');
    }
    return parts.join(' - ');
  }
}

String? _adminContextLabel(Map<String, dynamic> raw, bool isArabic) {
  final payable = _map(raw['payable']);
  final source =
      _string(raw['context_type']) ??
      _string(raw['payable_type']) ??
      _string(raw['provider_type']) ??
      _string(payable['context_type']) ??
      _string(payable['type']) ??
      _string(payable['kind']);
  if (source == null) return null;
  final normalized = source.toLowerCase();
  if (normalized.contains('pharmacy')) {
    return isArabic ? 'طلب صيدلية' : 'Pharmacy order';
  }
  if (normalized.contains('lab')) {
    return isArabic ? 'طلب معمل' : 'Lab order';
  }
  return null;
}

String friendlyAdminStatus(String status, bool isArabic) {
  if (!isArabic) return status.replaceAll('_', ' ');
  return switch (status) {
    'pending_review' => 'جاري المراجعة',
    'verified' => 'تم التحقق',
    'rejected' => 'مرفوض',
    'open' => 'مفتوح',
    'pending_admin' => 'في انتظار الإدارة',
    'pending_user' => 'في انتظار المستخدم',
    'resolved' => 'تم الحل',
    'closed' => 'مغلق',
    'requested' => 'مطلوب',
    'under_review' => 'قيد المراجعة',
    'approved' => 'مقبول',
    'processed' => 'تم التنفيذ',
    'investigating' => 'قيد التحقيق',
    'waiting_user' => 'في انتظار المستخدم',
    'waiting_provider' => 'في انتظار المزود',
    'pending_payment_review' => 'جاري مراجعة الدفع',
    'confirmed' => 'مؤكد',
    'paid' => 'تم الدفع',
    _ => status.replaceAll('_', ' '),
  };
}

Map<String, dynamic> _map(Object? raw) {
  if (raw is Map<String, dynamic>) return raw;
  if (raw is Map) return raw.map((key, value) => MapEntry('$key', value));
  return const {};
}

List<Object?> _list(Object? raw) => raw is List ? raw : const [];

int? _int(Object? raw) {
  if (raw is int) return raw;
  return int.tryParse(raw?.toString() ?? '');
}

String? _string(Object? raw) {
  final value = raw?.toString().trim();
  return value == null || value.isEmpty ? null : value;
}
