import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy.dart';
import 'package:flutter/material.dart';

class PharmacyCard extends StatelessWidget {
  const PharmacyCard({required this.pharmacy, required this.onTap, super.key});

  final Pharmacy pharmacy;
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
                child: const Icon(Icons.local_pharmacy_outlined),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      pharmacy.name,
                      style: Theme.of(context).textTheme.titleMedium,
                    ),
                    if (pharmacy.location.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        pharmacy.location,
                        style: const TextStyle(color: AppColors.muted),
                      ),
                    ],
                    if (pharmacy.phone?.isNotEmpty == true) ...[
                      const SizedBox(height: 4),
                      Text(pharmacy.phone!),
                    ],
                  ],
                ),
              ),
              const SizedBox(width: 8),
              FilledButton.tonal(
                onPressed: onTap,
                child: Text(l10n.get('products')),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
