import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/labs/domain/entities/lab.dart';
import 'package:flutter/material.dart';

class LabCard extends StatelessWidget {
  const LabCard({required this.lab, required this.onTap, super.key});

  final Lab lab;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              CircleAvatar(
                backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                child: const Icon(Icons.biotech_outlined),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      lab.name,
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    if (lab.location.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        lab.location,
                        style: const TextStyle(color: AppColors.muted),
                      ),
                    ],
                    if (lab.phone?.isNotEmpty == true) ...[
                      const SizedBox(height: 4),
                      Text(lab.phone!),
                    ],
                  ],
                ),
              ),
              FilledButton.tonal(
                onPressed: onTap,
                child: Text(l10n.get('tests')),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
