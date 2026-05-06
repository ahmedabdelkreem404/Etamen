import 'dart:io';

import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class ProofUploadBox extends StatelessWidget {
  const ProofUploadBox({
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
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              l10n.get('uploadProof'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            Text(l10n.get('proofUploadHint')),
            const SizedBox(height: 12),
            if (selectedFile == null)
              OutlinedButton.icon(
                onPressed: onPick,
                icon: const Icon(Icons.upload_file),
                label: Text(l10n.get('chooseProofImage')),
              )
            else ...[
              ClipRRect(
                borderRadius: BorderRadius.circular(12),
                child: Image.file(
                  File(selectedFile!.path),
                  height: 180,
                  fit: BoxFit.cover,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                selectedFile!.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
              ),
              TextButton.icon(
                onPressed: onClear,
                icon: const Icon(Icons.close),
                label: Text(l10n.get('removeFile')),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
