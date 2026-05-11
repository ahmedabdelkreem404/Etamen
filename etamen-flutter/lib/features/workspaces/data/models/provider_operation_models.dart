class ProviderOperationList {
  const ProviderOperationList({required this.items, required this.count});

  final List<ProviderOperationItem> items;
  final int count;

  factory ProviderOperationList.fromJson(Map<String, dynamic> json) {
    final items = _list(json['items'])
        .map((item) => ProviderOperationItem.fromJson(_map(item)))
        .toList(growable: false);
    final meta = _map(json['meta']);
    return ProviderOperationList(
      items: items,
      count: _int(meta['count']) ?? items.length,
    );
  }
}

class ProviderOperationItem {
  const ProviderOperationItem({required this.raw});

  final Map<String, dynamic> raw;

  factory ProviderOperationItem.fromJson(Map<String, dynamic> json) {
    return ProviderOperationItem(raw: json);
  }

  int get id => _int(raw['id']) ?? 0;

  String? get number => _string(raw['number']);

  String? get status => _string(raw['status']);

  String? get paymentStatus =>
      _string(raw['payment_status']) ?? _string(_map(raw['payment'])['status']);

  bool? get isActive {
    final value = raw['is_active'];
    return value is bool ? value : null;
  }

  bool get requiresPrescription => raw['requires_prescription'] == true;

  int? get stockQuantity => _int(raw['stock_quantity']);

  int? get resultTimeHours => _int(raw['result_time_hours']);

  String? get sampleType => _string(raw['sample_type']);

  String? get catalogType => _string(raw['catalog_type']);

  String? labelForStatus(bool isArabic) {
    final label = isArabic
        ? _string(raw['status_label_ar'])
        : _string(raw['status_label_en']);
    return label ?? (status == null ? null : friendlyStatus(status!, isArabic));
  }

  String? labelForPaymentStatus(bool isArabic) {
    final label = isArabic
        ? _string(raw['payment_status_label_ar'])
        : _string(raw['payment_status_label_en']);
    return label ??
        (paymentStatus == null
            ? null
            : friendlyStatus(paymentStatus!, isArabic));
  }

  String title(bool isArabic) {
    final direct = _localized(raw, isArabic);
    if (direct.isNotEmpty) return direct;

    final nestedKeys = [
      'patient',
      'plan',
      'class',
      'session_type',
      'slot',
      'department',
    ];
    for (final key in nestedKeys) {
      final nested = _localized(_map(raw[key]), isArabic);
      if (nested.isNotEmpty) return nested;
    }

    final doctorName = isArabic
        ? _string(raw['doctor_name_ar'])
        : _string(raw['doctor_name_en']);
    if (doctorName?.trim().isNotEmpty == true) return doctorName!;

    if (number?.trim().isNotEmpty == true) return number!;
    return '#$id';
  }

  String subtitle(bool isArabic) {
    final statusLabel = labelForStatus(isArabic);
    final paymentLabel = labelForPaymentStatus(isArabic);
    final parts = <String>[
      if (number?.trim().isNotEmpty == true) number!,
      if (statusLabel?.trim().isNotEmpty == true) statusLabel!,
      if (paymentLabel?.trim().isNotEmpty == true)
        '${isArabic ? 'الدفع' : 'Payment'}: $paymentLabel',
    ];

    final patient = _map(raw['patient']);
    final patientName = _string(patient['name']);
    if (patientName?.trim().isNotEmpty == true) {
      parts.add(patientName!);
    }

    final active = isActive;
    if (active != null) {
      parts.add(
        active
            ? (isArabic ? 'نشط' : 'Active')
            : (isArabic ? 'غير نشط' : 'Inactive'),
      );
    }
    if (requiresPrescription) {
      parts.add(isArabic ? 'يحتاج روشتة' : 'Prescription required');
    }
    final stockLabel = isArabic
        ? _string(raw['stock_label_ar'])
        : _string(raw['stock_label_en']);
    if (stockLabel?.trim().isNotEmpty == true) {
      parts.add(stockLabel!);
    } else if (stockQuantity != null) {
      parts.add('${isArabic ? 'المخزون' : 'Stock'}: $stockQuantity');
    }
    if (sampleType?.trim().isNotEmpty == true) {
      parts.add(sampleType!);
    }
    if (resultTimeHours != null) {
      parts.add('${resultTimeHours}h');
    }

    return parts.join(' - ');
  }

  String? amountLabel(bool isArabic) {
    final amount =
        _num(raw['total_amount']) ??
        _num(raw['grand_total']) ??
        _num(raw['price']) ??
        _num(raw['consultation_fee']);
    if (amount == null) return null;
    return '${amount.toStringAsFixed(0)} ${isArabic ? 'جنيه' : 'EGP'}';
  }

  ProviderOperationItem copyWithRaw(Map<String, dynamic> next) {
    return ProviderOperationItem(raw: next);
  }
}

class ProviderOperationActionResult {
  const ProviderOperationActionResult({required this.item});

  final ProviderOperationItem item;

  factory ProviderOperationActionResult.fromJson(Map<String, dynamic> json) {
    return ProviderOperationActionResult(
      item: ProviderOperationItem(raw: json),
    );
  }
}

String friendlyStatus(String status, bool isArabic) {
  if (!isArabic) return status.replaceAll('_', ' ');
  return switch (status) {
    'pending_payment' => 'في انتظار الدفع',
    'pending_payment_review' => 'جاري مراجعة الدفع',
    'awaiting_method' => 'في انتظار اختيار طريقة الدفع',
    'awaiting_payment' => 'في انتظار الدفع',
    'unpaid' => 'غير مدفوع',
    'pending_review' => 'جاري مراجعة الدفع',
    'verified' => 'تم التحقق',
    'paid' => 'تم الدفع',
    'confirmed' => 'مؤكد',
    'accepted' => 'مقبول',
    'active' => 'نشط',
    'in_progress' => 'قيد التنفيذ',
    'processing' => 'قيد التنفيذ',
    'result_ready' => 'النتيجة جاهزة',
    'completed' => 'مكتمل',
    'cancelled' => 'ملغي',
    'cancelled_by_user' => 'ملغي بواسطة العميل',
    'cancelled_by_patient' => 'ملغي بواسطة المريض',
    'cancelled_by_provider' => 'ملغي من المزود',
    'cancelled_by_doctor' => 'ملغي من الطبيب',
    'cancelled_by_coach' => 'ملغي من الكوتش',
    'rejected' => 'مرفوض',
    'lab_review' => 'مراجعة المعمل',
    'pharmacy_review' => 'مراجعة الصيدلية',
    'sample_collected' => 'تم جمع العينة',
    'sample_scheduled' => 'تم تحديد موعد العينة',
    'out_for_delivery' => 'خارج للتوصيل',
    'ready_for_pickup' => 'جاهز للاستلام',
    'preparing' => 'قيد التجهيز',
    'delivered' => 'تم التسليم',
    _ => status.replaceAll('_', ' '),
  };
}

Map<String, dynamic> _map(Object? raw) {
  if (raw is Map<String, dynamic>) return raw;
  if (raw is Map) {
    return raw.map((key, value) => MapEntry(key.toString(), value));
  }
  return const {};
}

List<Object?> _list(Object? raw) {
  if (raw is List) return raw;
  return const [];
}

int? _int(Object? raw) {
  if (raw is int) return raw;
  return int.tryParse(raw?.toString() ?? '');
}

num? _num(Object? raw) {
  if (raw is num) return raw;
  return num.tryParse(raw?.toString() ?? '');
}

String? _string(Object? raw) {
  final value = raw?.toString();
  return value == null || value.trim().isEmpty ? null : value;
}

String _localized(Map<String, dynamic> json, bool isArabic) {
  final ar = _string(json['name_ar']) ?? _string(json['title_ar']);
  final en = _string(json['name_en']) ?? _string(json['title_en']);
  if (isArabic) return ar ?? en ?? '';
  return en ?? ar ?? '';
}
