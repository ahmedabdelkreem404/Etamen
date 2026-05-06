import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_labels.dart';
import 'package:flutter/material.dart';

class NotificationPriorityChip extends StatelessWidget {
  const NotificationPriorityChip({required this.priority, super.key});

  final NotificationPriority priority;

  @override
  Widget build(BuildContext context) {
    final color = switch (priority) {
      NotificationPriority.urgent => AppColors.danger,
      NotificationPriority.high => AppColors.warning,
      NotificationPriority.normal => AppColors.info,
      NotificationPriority.low => AppColors.muted,
      NotificationPriority.unknown => AppColors.muted,
    };
    return Chip(
      visualDensity: VisualDensity.compact,
      backgroundColor: color.withValues(alpha: 0.08),
      side: BorderSide(color: color.withValues(alpha: 0.18)),
      label: Text(
        notificationPriorityLabel(context, priority),
        style: TextStyle(color: color),
      ),
    );
  }
}
