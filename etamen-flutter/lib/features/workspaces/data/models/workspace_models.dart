class WorkspaceUser {
  const WorkspaceUser({
    required this.id,
    required this.name,
    required this.email,
  });

  final int id;
  final String name;
  final String email;

  factory WorkspaceUser.fromJson(Map<String, dynamic> json) {
    return WorkspaceUser(
      id: _int(json['id']) ?? 0,
      name: (json['name'] ?? '').toString(),
      email: (json['email'] ?? '').toString(),
    );
  }
}

class WorkspaceSummary {
  const WorkspaceSummary({
    required this.type,
    required this.key,
    required this.permissions,
    this.providerId,
    this.providerType,
    this.providerNameAr,
    this.providerNameEn,
    this.role,
    this.isOwner = false,
    this.status,
    this.labelAr,
    this.labelEn,
  });

  final String type;
  final String key;
  final int? providerId;
  final String? providerType;
  final String? providerNameAr;
  final String? providerNameEn;
  final String? role;
  final bool isOwner;
  final String? status;
  final String? labelAr;
  final String? labelEn;
  final List<String> permissions;

  bool get isPatient => type == 'patient';

  bool get isProvider => type == 'provider' && providerId != null;

  bool get isPlatformAdmin => type == 'platform_admin';

  String label(bool isArabic) {
    if (isProvider) {
      return isArabic
          ? (providerNameAr?.trim().isNotEmpty == true
                ? providerNameAr!
                : providerNameEn ?? '')
          : (providerNameEn?.trim().isNotEmpty == true
                ? providerNameEn!
                : providerNameAr ?? '');
    }
    return isArabic
        ? (labelAr?.trim().isNotEmpty == true ? labelAr! : labelEn ?? key)
        : (labelEn?.trim().isNotEmpty == true ? labelEn! : labelAr ?? key);
  }

  String typeLabel(bool isArabic) {
    final value = providerType ?? type;
    if (!isArabic) return value.replaceAll('_', ' ');
    return switch (value) {
      'patient' => 'مريض',
      'platform_admin' => 'إدارة المنصة',
      'doctor' => 'طبيب',
      'hospital' => 'مستشفى',
      'radiology' => 'مركز أشعة',
      'pharmacy' => 'صيدلية',
      'lab' => 'معمل',
      'gym' => 'جيم',
      'fitness_coach' => 'كابتن جيم',
      'nutrition_coach' => 'كوتش تغذية',
      _ => value,
    };
  }

  factory WorkspaceSummary.fromJson(Map<String, dynamic> json) {
    return WorkspaceSummary(
      type: (json['type'] ?? '').toString(),
      key: (json['key'] ?? '').toString(),
      providerId: _int(json['provider_id']),
      providerType: json['provider_type']?.toString(),
      providerNameAr: json['provider_name_ar']?.toString(),
      providerNameEn: json['provider_name_en']?.toString(),
      role: json['role']?.toString(),
      isOwner: _bool(json['is_owner']),
      status: json['status']?.toString(),
      labelAr: json['label_ar']?.toString(),
      labelEn: json['label_en']?.toString(),
      permissions: _list(
        json['permissions'],
      ).map((item) => item.toString()).toList(growable: false),
    );
  }
}

class WorkspacesResponse {
  const WorkspacesResponse({
    required this.user,
    required this.defaultWorkspace,
    required this.availableWorkspaces,
  });

  final WorkspaceUser user;
  final String defaultWorkspace;
  final List<WorkspaceSummary> availableWorkspaces;

  WorkspaceSummary? workspaceByKey(String? key) {
    if (key == null) return null;
    for (final workspace in availableWorkspaces) {
      if (workspace.key == key) return workspace;
    }
    return null;
  }

  factory WorkspacesResponse.fromJson(Map<String, dynamic> json) {
    return WorkspacesResponse(
      user: WorkspaceUser.fromJson(_map(json['user'])),
      defaultWorkspace: (json['default_workspace'] ?? 'patient').toString(),
      availableWorkspaces: _list(json['available_workspaces'])
          .map((item) => WorkspaceSummary.fromJson(_map(item)))
          .toList(growable: false),
    );
  }
}

class DashboardProviderSummary {
  const DashboardProviderSummary({
    required this.id,
    required this.type,
    required this.nameAr,
    this.nameEn,
    this.status,
    this.isActive = false,
    this.primaryAreaName,
    this.primaryCityName,
  });

  final int id;
  final String type;
  final String nameAr;
  final String? nameEn;
  final String? status;
  final bool isActive;
  final String? primaryAreaName;
  final String? primaryCityName;

  String name(bool isArabic) {
    return isArabic
        ? (nameAr.trim().isNotEmpty ? nameAr : nameEn ?? '')
        : (nameEn?.trim().isNotEmpty == true ? nameEn! : nameAr);
  }

  factory DashboardProviderSummary.fromJson(Map<String, dynamic> json) {
    return DashboardProviderSummary(
      id: _int(json['id']) ?? 0,
      type: (json['type'] ?? '').toString(),
      nameAr: (json['name_ar'] ?? json['name_en'] ?? '').toString(),
      nameEn: json['name_en']?.toString(),
      status: json['status']?.toString(),
      isActive: _bool(json['is_active']),
      primaryAreaName: json['primary_area_name']?.toString(),
      primaryCityName: json['primary_city_name']?.toString(),
    );
  }
}

class DashboardCard {
  const DashboardCard({
    required this.key,
    required this.labelAr,
    required this.labelEn,
    required this.value,
  });

  final String key;
  final String labelAr;
  final String labelEn;
  final num value;

  String label(bool isArabic) => isArabic ? labelAr : labelEn;

  factory DashboardCard.fromJson(Map<String, dynamic> json) {
    return DashboardCard(
      key: (json['key'] ?? '').toString(),
      labelAr: (json['label_ar'] ?? json['label_en'] ?? '').toString(),
      labelEn: (json['label_en'] ?? json['label_ar'] ?? '').toString(),
      value: num.tryParse((json['value'] ?? 0).toString()) ?? 0,
    );
  }
}

class DashboardQuickAction {
  const DashboardQuickAction({
    required this.key,
    required this.labelAr,
    required this.labelEn,
  });

  final String key;
  final String labelAr;
  final String labelEn;

  String label(bool isArabic) => isArabic ? labelAr : labelEn;

  factory DashboardQuickAction.fromJson(Map<String, dynamic> json) {
    return DashboardQuickAction(
      key: (json['key'] ?? '').toString(),
      labelAr: (json['label_ar'] ?? json['label_en'] ?? '').toString(),
      labelEn: (json['label_en'] ?? json['label_ar'] ?? '').toString(),
    );
  }
}

class ProviderDashboard {
  const ProviderDashboard({
    required this.provider,
    required this.role,
    required this.permissions,
    required this.todayCount,
    required this.pendingPaymentReviewCount,
    required this.pendingActionsCount,
    required this.summaryCards,
    required this.quickActions,
    this.isOwner = false,
  });

  final DashboardProviderSummary provider;
  final String role;
  final bool isOwner;
  final List<String> permissions;
  final int todayCount;
  final int pendingPaymentReviewCount;
  final int pendingActionsCount;
  final List<DashboardCard> summaryCards;
  final List<DashboardQuickAction> quickActions;

  factory ProviderDashboard.fromJson(Map<String, dynamic> json) {
    return ProviderDashboard(
      provider: DashboardProviderSummary.fromJson(_map(json['provider'])),
      role: (json['role'] ?? '').toString(),
      isOwner: _bool(json['is_owner']),
      permissions: _list(
        json['permissions'],
      ).map((item) => item.toString()).toList(growable: false),
      todayCount: _int(json['today_count']) ?? 0,
      pendingPaymentReviewCount:
          _int(json['pending_payment_review_count']) ?? 0,
      pendingActionsCount: _int(json['pending_actions_count']) ?? 0,
      summaryCards: _list(json['summary_cards'])
          .map((item) => DashboardCard.fromJson(_map(item)))
          .toList(growable: false),
      quickActions: _list(json['quick_actions'])
          .map((item) => DashboardQuickAction.fromJson(_map(item)))
          .toList(growable: false),
    );
  }
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

bool _bool(Object? raw) {
  if (raw is bool) return raw;
  final value = raw?.toString().toLowerCase();
  return value == '1' || value == 'true';
}
