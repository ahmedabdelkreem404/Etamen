import 'dart:io';

import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/app_text_field.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/payments/data/models/upload_payment_proof_request.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_status_controller.dart';
import 'package:etamen_app/features/payments/presentation/widgets/manual_instructions_card.dart';
import 'package:etamen_app/features/payments/presentation/widgets/proof_upload_box.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

class ManualPaymentPage extends ConsumerStatefulWidget {
  const ManualPaymentPage({
    required this.paymentId,
    required this.methodId,
    this.appointmentId,
    this.pharmacyOrderId,
    this.labOrderId,
    this.radiologyOrderId,
    this.gymBookingId,
    this.coachBookingId,
    super.key,
  });

  final int paymentId;
  final int methodId;
  final int? appointmentId;
  final int? pharmacyOrderId;
  final int? labOrderId;
  final int? radiologyOrderId;
  final int? gymBookingId;
  final int? coachBookingId;

  @override
  ConsumerState<ManualPaymentPage> createState() => _ManualPaymentPageState();
}

class _ManualPaymentPageState extends ConsumerState<ManualPaymentPage> {
  final _referenceController = TextEditingController();
  final _senderPhoneController = TextEditingController();
  final _notesController = TextEditingController();
  final _imagePicker = ImagePicker();
  XFile? _selectedFile;
  String? _localError;
  bool _didSelectMethod = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_didSelectMethod) return;
    _didSelectMethod = true;
    Future.microtask(() {
      ref
          .read(paymentControllerProvider(widget.paymentId).notifier)
          .selectManualMethod(
            paymentId: widget.paymentId,
            paymentMethodId: widget.methodId,
          );
    });
  }

  @override
  void dispose() {
    _referenceController.dispose();
    _senderPhoneController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(paymentControllerProvider(widget.paymentId));
    final statusState = ref.watch(
      paymentStatusControllerProvider(widget.paymentId),
    );
    final instructions = state.selection?.instructions(l10n.isArabic);

    return AppScaffold(
      title: l10n.get('uploadProof'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (statusState.status != null)
            SoftMedicalCard(
              padding: const EdgeInsets.all(14),
              child: Row(
                children: [
                  Container(
                    width: 46,
                    height: 46,
                    decoration: BoxDecoration(
                      color: AppColors.medicalMint,
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: const Icon(
                      Icons.payments_outlined,
                      color: AppColors.primary,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          l10n.get('amount'),
                          style: Theme.of(context).textTheme.labelMedium
                              ?.copyWith(color: AppColors.muted),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          '${statusState.status!.amount} ${statusState.status!.currency}',
                          style: Theme.of(context).textTheme.titleLarge
                              ?.copyWith(fontWeight: FontWeight.w900),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          if (statusState.status != null) const SizedBox(height: 12),
          SoftMedicalCard(
            padding: const EdgeInsets.all(14),
            child: Row(
              children: [
                const Icon(
                  Icons.verified_user_outlined,
                  color: AppColors.primary,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    l10n.get('paymentAdminReviewNotice'),
                    style: Theme.of(context).textTheme.bodySmall?.copyWith(
                      color: AppColors.softText,
                      height: 1.35,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          if (state.isSelectingMethod)
            const LoadingView()
          else
            ManualInstructionsCard(instructions: instructions),
          const SizedBox(height: 12),
          ProofUploadBox(
            selectedFile: _selectedFile,
            onPick: _pickProof,
            onClear: () => setState(() {
              _selectedFile = null;
              _localError = null;
            }),
          ),
          const SizedBox(height: 12),
          AppTextField(
            controller: _referenceController,
            label: l10n.get('referenceNumber'),
          ),
          const SizedBox(height: 12),
          AppTextField(
            controller: _senderPhoneController,
            label: l10n.get('senderPhone'),
            keyboardType: TextInputType.phone,
          ),
          const SizedBox(height: 12),
          AppTextField(
            controller: _notesController,
            label: l10n.get('notes'),
            maxLines: 3,
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
            label: l10n.get('submitProof'),
            isLoading: state.isUploadingProof,
            onPressed: state.isUploadingProof ? null : _submitProof,
          ),
          const SizedBox(height: 12),
          Text(
            l10n.get('doNotCloseBeforeProof'),
            style: const TextStyle(color: AppColors.muted),
          ),
        ],
      ),
    );
  }

  Future<void> _pickProof() async {
    final file = await _imagePicker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 92,
    );
    if (file == null) return;

    final length = await File(file.path).length();
    if (length > UploadPaymentProofRequest.maxLocalBytes) {
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

  Future<void> _submitProof() async {
    final file = _selectedFile;
    if (file == null) {
      setState(
        () => _localError = AppLocalizations.of(context).get('proofRequired'),
      );
      return;
    }

    final status = await ref
        .read(paymentControllerProvider(widget.paymentId).notifier)
        .uploadProof(
          paymentId: widget.paymentId,
          request: UploadPaymentProofRequest(
            filePath: file.path,
            fileName: file.name,
            referenceNumber: _referenceController.text.trim(),
            senderPhone: _senderPhoneController.text.trim(),
            notes: _notesController.text.trim(),
          ),
        );

    if (status != null && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(AppLocalizations.of(context).get('proofUploaded')),
        ),
      );
      context.go(
        RouteNames.paymentStatus(
          widget.paymentId,
          appointmentId: widget.appointmentId,
          pharmacyOrderId: widget.pharmacyOrderId,
          labOrderId: widget.labOrderId,
          radiologyOrderId: widget.radiologyOrderId,
          gymBookingId: widget.gymBookingId,
          coachBookingId: widget.coachBookingId,
        ),
      );
    }
  }
}
