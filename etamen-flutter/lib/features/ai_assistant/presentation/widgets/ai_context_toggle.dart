import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:flutter/material.dart';

class AiContextToggle extends StatelessWidget {
  const AiContextToggle({
    required this.value,
    required this.onChanged,
    this.onPreview,
    super.key,
  });

  final bool value;
  final ValueChanged<bool> onChanged;
  final VoidCallback? onPreview;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: SwitchListTile(
        value: value,
        onChanged: onChanged,
        secondary: const Icon(Icons.health_and_safety_outlined),
        title: Text(l10n.get('healthContext')),
        subtitle: Text(
          value ? l10n.get('contextEnabled') : l10n.get('contextDisabled'),
        ),
        isThreeLine: false,
      ),
    );
  }
}
