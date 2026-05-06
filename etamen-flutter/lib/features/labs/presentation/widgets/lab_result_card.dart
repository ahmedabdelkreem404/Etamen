import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_result.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';

class LabResultCard extends StatelessWidget {
  const LabResultCard({
    required this.result,
    required this.isDownloading,
    required this.onDownload,
    super.key,
  });

  final LabResult result;
  final bool isDownloading;
  final Future<void> Function() onDownload;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.picture_as_pdf_outlined),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    result.fileName ?? l10n.get('labResult'),
                    style: Theme.of(context).textTheme.titleMedium,
                  ),
                ),
              ],
            ),
            if (result.uploadedAt != null) ...[
              const SizedBox(height: 8),
              Text(
                DateFormat(
                  'd MMM yyyy, h:mm a',
                ).format(result.uploadedAt!.toLocal()),
                style: const TextStyle(color: AppColors.muted),
              ),
            ],
            if (result.notes?.isNotEmpty == true) ...[
              const SizedBox(height: 8),
              Text(result.notes!),
            ],
            const SizedBox(height: 12),
            AppButton(
              label: l10n.get('downloadResult'),
              isLoading: isDownloading,
              onPressed: isDownloading ? null : () => onDownload(),
            ),
          ],
        ),
      ),
    );
  }
}
