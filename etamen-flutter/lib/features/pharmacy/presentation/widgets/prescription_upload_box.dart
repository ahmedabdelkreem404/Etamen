import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class PrescriptionUploadBox extends StatelessWidget {
  const PrescriptionUploadBox({
    required this.selectedFile,
    required this.onPick,
    required this.onClear,
    super.key,
  });

  final XFile? selectedFile;
  final VoidCallback onPick;
  final VoidCallback onClear;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              l10n.get('uploadPrescription'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            Text(
              l10n.get('prescriptionPrivate'),
              style: const TextStyle(color: AppColors.muted),
            ),
            const SizedBox(height: 12),
            if (selectedFile == null)
              OutlinedButton.icon(
                onPressed: onPick,
                icon: const Icon(Icons.image_outlined),
                label: Text(l10n.get('choosePrescriptionImage')),
              )
            else
              Row(
                children: [
                  const Icon(Icons.insert_drive_file_outlined),
                  const SizedBox(width: 8),
                  Expanded(child: Text(selectedFile!.name)),
                  IconButton(
                    onPressed: onClear,
                    icon: const Icon(Icons.close),
                    tooltip: l10n.get('removeFile'),
                  ),
                ],
              ),
          ],
        ),
      ),
    );
  }
}
