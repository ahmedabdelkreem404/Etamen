import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';
import 'package:flutter/material.dart';

class LabTestCard extends StatelessWidget {
  const LabTestCard({
    required this.test,
    required this.quantity,
    required this.onAdd,
    required this.onIncrease,
    required this.onDecrease,
    super.key,
  });

  final LabTest test;
  final int quantity;
  final VoidCallback onAdd;
  final VoidCallback onIncrease;
  final VoidCallback onDecrease;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(test.name, style: Theme.of(context).textTheme.titleMedium),
            if (test.description?.isNotEmpty == true) ...[
              const SizedBox(height: 4),
              Text(
                test.description!,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: AppColors.muted),
              ),
            ],
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 4,
              children: [
                if (test.sampleType?.isNotEmpty == true)
                  Chip(label: Text(test.sampleType!)),
                if (test.resultTimeHours != null)
                  Chip(label: Text('${test.resultTimeHours}h')),
              ],
            ),
            if (test.preparationInstructions?.trim().isNotEmpty == true) ...[
              const SizedBox(height: 4),
              ExpansionTile(
                tilePadding: EdgeInsets.zero,
                childrenPadding: EdgeInsets.zero,
                title: Text(l10n.isArabic ? 'تعليمات التحضير' : 'Preparation'),
                children: [
                  Align(
                    alignment: AlignmentDirectional.centerStart,
                    child: Text(
                      test.preparationInstructions!,
                      style: const TextStyle(color: AppColors.muted),
                    ),
                  ),
                ],
              ),
            ],
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${test.price} ${test.currency}',
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                ),
                _QuantityAction(
                  quantity: quantity,
                  addLabel: l10n.get('addToLabOrder'),
                  onAdd: onAdd,
                  onIncrease: onIncrease,
                  onDecrease: onDecrease,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _QuantityAction extends StatelessWidget {
  const _QuantityAction({
    required this.quantity,
    required this.addLabel,
    required this.onAdd,
    required this.onIncrease,
    required this.onDecrease,
  });

  final int quantity;
  final String addLabel;
  final VoidCallback onAdd;
  final VoidCallback onIncrease;
  final VoidCallback onDecrease;

  @override
  Widget build(BuildContext context) {
    if (quantity == 0) {
      return FilledButton.icon(
        onPressed: onAdd,
        icon: const Icon(Icons.add),
        label: Text(addLabel),
      );
    }
    return Row(
      children: [
        IconButton(
          onPressed: onDecrease,
          icon: const Icon(Icons.remove_circle_outline),
        ),
        Text('$quantity', style: const TextStyle(fontWeight: FontWeight.w800)),
        IconButton(
          onPressed: onIncrease,
          icon: const Icon(Icons.add_circle_outline),
        ),
      ],
    );
  }
}
