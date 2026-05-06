import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:flutter/material.dart';

class AiInputBar extends StatelessWidget {
  const AiInputBar({
    required this.controller,
    required this.onSend,
    this.isSending = false,
    super.key,
  });

  final TextEditingController controller;
  final VoidCallback onSend;
  final bool isSending;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return SafeArea(
      top: false,
      child: Padding(
        padding: const EdgeInsets.fromLTRB(12, 8, 12, 12),
        child: Row(
          children: [
            Expanded(
              child: TextField(
                controller: controller,
                maxLength: 4000,
                minLines: 1,
                maxLines: 5,
                enabled: !isSending,
                decoration: InputDecoration(
                  hintText: l10n.get('writeYourMessage'),
                  counterText: '',
                  border: const OutlineInputBorder(),
                ),
              ),
            ),
            const SizedBox(width: 8),
            IconButton.filled(
              tooltip: l10n.get('send'),
              onPressed: isSending ? null : onSend,
              icon: isSending
                  ? const SizedBox(
                      width: 18,
                      height: 18,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.send),
            ),
          ],
        ),
      ),
    );
  }
}
