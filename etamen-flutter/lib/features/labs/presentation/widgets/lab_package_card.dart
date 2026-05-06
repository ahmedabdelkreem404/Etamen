import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:flutter/material.dart';

class LabPackageCard extends StatelessWidget {
  const LabPackageCard({
    required this.package,
    required this.quantity,
    required this.onAdd,
    required this.onIncrease,
    required this.onDecrease,
    super.key,
  });

  final LabPackage package;
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
            Text(package.name, style: Theme.of(context).textTheme.titleMedium),
            if (package.description?.isNotEmpty == true) ...[
              const SizedBox(height: 4),
              Text(
                package.description!,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: AppColors.muted),
              ),
            ],
            if (package.tests.isNotEmpty) ...[
              const SizedBox(height: 8),
              Text('${package.tests.length} ${l10n.get('tests')}'),
            ],
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: Text(
                    '${package.price} ${package.currency}',
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                ),
                quantity == 0
                    ? FilledButton.icon(
                        onPressed: onAdd,
                        icon: const Icon(Icons.add),
                        label: Text(l10n.get('addToLabOrder')),
                      )
                    : Row(
                        children: [
                          IconButton(
                            onPressed: onDecrease,
                            icon: const Icon(Icons.remove_circle_outline),
                          ),
                          Text(
                            '$quantity',
                            style: const TextStyle(fontWeight: FontWeight.w800),
                          ),
                          IconButton(
                            onPressed: onIncrease,
                            icon: const Icon(Icons.add_circle_outline),
                          ),
                        ],
                      ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
