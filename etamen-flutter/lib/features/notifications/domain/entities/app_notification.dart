class AppNotification {
  const AppNotification({
    required this.id,
    required this.category,
    required this.type,
    required this.title,
    required this.body,
    required this.priority,
    this.data = const {},
    this.readAt,
    this.actionUrl,
    this.createdAt,
    this.updatedAt,
  });

  final int id;
  final NotificationCategory category;
  final String type;
  final String title;
  final String body;
  final Map<String, dynamic> data;
  final NotificationPriority priority;
  final DateTime? readAt;
  final String? actionUrl;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  bool get isRead => readAt != null;
}

enum NotificationCategory {
  appointments('appointments'),
  payments('payments'),
  pharmacy('pharmacy'),
  labs('labs'),
  medications('medications'),
  carePlans('care_plans'),
  wallet('wallet'),
  aiSafety('ai_safety'),
  system('system'),
  unknown('unknown');

  const NotificationCategory(this.wireValue);

  final String wireValue;

  static NotificationCategory fromWire(Object? value) {
    final normalized = value?.toString();
    return NotificationCategory.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => NotificationCategory.unknown,
    );
  }
}

enum NotificationPriority {
  low('low'),
  normal('normal'),
  high('high'),
  urgent('urgent'),
  unknown('unknown');

  const NotificationPriority(this.wireValue);

  final String wireValue;

  static NotificationPriority fromWire(Object? value) {
    final normalized = value?.toString();
    return NotificationPriority.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => NotificationPriority.unknown,
    );
  }
}
