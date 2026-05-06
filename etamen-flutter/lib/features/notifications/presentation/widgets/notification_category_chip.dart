import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_labels.dart';
import 'package:flutter/material.dart';

class NotificationCategoryChip extends StatelessWidget {
  const NotificationCategoryChip({required this.category, super.key});

  final NotificationCategory category;

  @override
  Widget build(BuildContext context) {
    return Chip(
      visualDensity: VisualDensity.compact,
      backgroundColor: AppColors.primary.withValues(alpha: 0.08),
      side: BorderSide(color: AppColors.primary.withValues(alpha: 0.18)),
      label: Text(
        notificationCategoryLabel(context, category),
        style: const TextStyle(color: AppColors.primary),
      ),
    );
  }
}
