import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:flutter/material.dart';

class CancelAppointmentDialog extends StatefulWidget {
  const CancelAppointmentDialog({super.key});

  @override
  State<CancelAppointmentDialog> createState() =>
      _CancelAppointmentDialogState();
}

class _CancelAppointmentDialogState extends State<CancelAppointmentDialog> {
  final _reasonController = TextEditingController();

  @override
  void dispose() {
    _reasonController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return AlertDialog(
      title: Text(l10n.get('cancelAppointment')),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(l10n.get('confirmCancelAppointment')),
          const SizedBox(height: 12),
          TextField(
            controller: _reasonController,
            minLines: 2,
            maxLines: 4,
            decoration: InputDecoration(labelText: l10n.get('cancelReason')),
          ),
        ],
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: Text(l10n.get('back')),
        ),
        FilledButton(
          onPressed: () => Navigator.of(context).pop(_reasonController.text),
          child: Text(l10n.get('cancelAppointment')),
        ),
      ],
    );
  }
}
