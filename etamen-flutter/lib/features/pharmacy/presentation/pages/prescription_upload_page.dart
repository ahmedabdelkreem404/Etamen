import 'dart:io';

import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/pharmacy/data/models/upload_prescription_request.dart';
import 'package:etamen_app/features/pharmacy/presentation/providers/pharmacy_providers.dart';
import 'package:etamen_app/features/pharmacy/presentation/widgets/prescription_upload_box.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

class PrescriptionUploadPage extends ConsumerStatefulWidget {
  const PrescriptionUploadPage({super.key});

  @override
  ConsumerState<PrescriptionUploadPage> createState() =>
      _PrescriptionUploadPageState();
}

class _PrescriptionUploadPageState
    extends ConsumerState<PrescriptionUploadPage> {
  final _picker = ImagePicker();
  XFile? _selectedFile;
  String? _localError;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(prescriptionUploadControllerProvider);

    return AppScaffold(
      title: l10n.get('uploadPrescription'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          PrescriptionUploadBox(
            selectedFile: _selectedFile,
            onPick: _pick,
            onClear: () => setState(() {
              _selectedFile = null;
              _localError = null;
            }),
          ),
          if (_localError != null || state.error != null) ...[
            const SizedBox(height: 12),
            Text(
              _localError ?? state.error!.message,
              style: const TextStyle(color: AppColors.danger),
            ),
          ],
          const SizedBox(height: 24),
          AppButton(
            label: l10n.get('uploadPrescription'),
            isLoading: state.isUploading,
            onPressed: state.isUploading ? null : _upload,
          ),
        ],
      ),
    );
  }

  Future<void> _pick() async {
    final file = await _picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 92,
    );
    if (file == null) return;
    final length = await File(file.path).length();
    if (length > UploadPrescriptionRequest.maxLocalBytes) {
      setState(
        () => _localError = AppLocalizations.of(context).get('fileTooLarge'),
      );
      return;
    }
    setState(() {
      _selectedFile = file;
      _localError = null;
    });
  }

  Future<void> _upload() async {
    final file = _selectedFile;
    final cart = ref.read(pharmacyCartControllerProvider);
    final l10n = AppLocalizations.of(context);

    if (file == null) {
      setState(() => _localError = l10n.get('choosePrescriptionImage'));
      return;
    }

    final prescription = await ref
        .read(prescriptionUploadControllerProvider.notifier)
        .upload(
          UploadPrescriptionRequest(
            filePath: file.path,
            fileName: file.name,
            pharmacyId: cart.pharmacyId,
          ),
        );

    if (prescription == null || !mounted) return;
    ref
        .read(pharmacyCartControllerProvider.notifier)
        .attachPrescription(prescription);
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(l10n.get('prescriptionUploaded'))));
    context.pop();
  }
}
