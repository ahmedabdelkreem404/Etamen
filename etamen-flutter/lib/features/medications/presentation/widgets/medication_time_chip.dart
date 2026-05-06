import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:flutter/material.dart';

class MedicationTimeChip extends StatelessWidget {
  const MedicationTimeChip({required this.time, this.label, super.key});

  final String time;
  final String? label;

  @override
  Widget build(BuildContext context) {
    return Chip(
      avatar: const Icon(Icons.schedule_outlined, size: 18),
      side: BorderSide(color: AppColors.primary.withValues(alpha: 0.18)),
      backgroundColor: AppColors.primary.withValues(alpha: 0.06),
      label: Text(label?.trim().isNotEmpty == true ? '$time - $label' : time),
    );
  }
}
