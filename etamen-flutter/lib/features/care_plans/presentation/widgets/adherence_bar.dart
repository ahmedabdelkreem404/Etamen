import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:flutter/material.dart';

class AdherenceBar extends StatelessWidget {
  const AdherenceBar({required this.value, super.key});

  final double? value;

  @override
  Widget build(BuildContext context) {
    final percentage = (value ?? 0).clamp(0, 100).toDouble();
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        LinearProgressIndicator(
          value: percentage / 100,
          minHeight: 8,
          borderRadius: BorderRadius.circular(8),
          color: AppColors.primary,
        ),
        const SizedBox(height: 6),
        Text('${percentage.toStringAsFixed(0)}%'),
      ],
    );
  }
}
