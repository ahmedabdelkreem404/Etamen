import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_labels.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_status_chip.dart';
import 'package:flutter/material.dart';

class CarePlanCard extends StatelessWidget {
  const CarePlanCard({
    required this.plan,
    required this.onTap,
    required this.onProgress,
    super.key,
  });

  final CarePlan plan;
  final VoidCallback onTap;
  final VoidCallback onProgress;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  CircleAvatar(
                    backgroundColor: AppColors.primary.withValues(alpha: 0.1),
                    child: const Icon(Icons.assignment_outlined),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          plan.title,
                          style: Theme.of(context).textTheme.titleMedium,
                        ),
                        Text(
                          carePlanTypeLabel(context, plan.planType),
                          style: const TextStyle(color: AppColors.muted),
                        ),
                      ],
                    ),
                  ),
                  CarePlanStatusChip(status: plan.status),
                ],
              ),
              const SizedBox(height: 10),
              if (plan.goalText?.isNotEmpty == true) Text(plan.goalText!),
              const SizedBox(height: 8),
              Text(
                '${plan.startDate ?? '--'}${plan.endDate == null ? '' : ' - ${plan.endDate}'}',
                style: const TextStyle(color: AppColors.muted),
              ),
              if (plan.providerName?.isNotEmpty == true) ...[
                const SizedBox(height: 6),
                Text(
                  plan.providerName!,
                  style: const TextStyle(color: AppColors.muted),
                ),
              ],
              const SizedBox(height: 8),
              Wrap(
                spacing: 8,
                children: [
                  TextButton(
                    onPressed: onTap,
                    child: Text(l10n.get('details')),
                  ),
                  TextButton(
                    onPressed: onProgress,
                    child: Text(l10n.get('progress')),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
