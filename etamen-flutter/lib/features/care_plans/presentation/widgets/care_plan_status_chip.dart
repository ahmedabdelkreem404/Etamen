import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_labels.dart';
import 'package:flutter/material.dart';

class CarePlanStatusChip extends StatelessWidget {
  const CarePlanStatusChip({required this.status, super.key});

  final CarePlanStatus status;

  @override
  Widget build(BuildContext context) {
    final color = switch (status) {
      CarePlanStatus.active => AppColors.success,
      CarePlanStatus.paused => AppColors.warning,
      CarePlanStatus.completed => AppColors.info,
      CarePlanStatus.cancelled => AppColors.muted,
      CarePlanStatus.draft => AppColors.muted,
      CarePlanStatus.unknown => AppColors.muted,
    };
    return Chip(
      visualDensity: VisualDensity.compact,
      backgroundColor: color.withValues(alpha: 0.08),
      side: BorderSide(color: color.withValues(alpha: 0.2)),
      label: Text(
        carePlanStatusLabel(context, status),
        style: TextStyle(color: color),
      ),
    );
  }
}
