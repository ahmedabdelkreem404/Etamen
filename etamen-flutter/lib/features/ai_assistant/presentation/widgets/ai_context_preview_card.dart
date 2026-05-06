import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_context_preview.dart';
import 'package:flutter/material.dart';

class AiContextPreviewCard extends StatelessWidget {
  const AiContextPreviewCard({required this.preview, super.key});

  final AiContextPreview preview;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final sections = <Widget>[
      if (preview.age != null || preview.gender != null)
        _Section(
          title: l10n.get('basicInfo'),
          lines: [
            if (preview.age != null) '${l10n.get('age')}: ${preview.age}',
            if (preview.gender != null)
              '${l10n.get('gender')}: ${preview.gender}',
          ],
        ),
      if (preview.latestVitals.isNotEmpty)
        _Section(
          title: l10n.get('latestVitals'),
          lines: preview.latestVitals
              .map(
                (item) =>
                    item.entries.map((e) => '${e.key}: ${e.value}').join(' • '),
              )
              .toList(growable: false),
        ),
      if (preview.chronicDiseases.isNotEmpty)
        _Section(
          title: l10n.get('chronicDiseases'),
          lines: preview.chronicDiseases,
        ),
      if (preview.allergies.isNotEmpty)
        _Section(title: l10n.get('allergies'), lines: preview.allergies),
      if (preview.currentMedications.isNotEmpty)
        _Section(
          title: l10n.get('currentMedications'),
          lines: preview.currentMedications,
        ),
      if (preview.medicationAdherence?.isNotEmpty == true)
        _Section(
          title: l10n.get('adherence'),
          lines: preview.medicationAdherence!.entries
              .map((entry) => '${entry.key}: ${entry.value}')
              .toList(growable: false),
        ),
      if (preview.carePlanSummary.isNotEmpty)
        _Section(
          title: l10n.get('carePlans'),
          lines: preview.carePlanSummary
              .map(
                (item) =>
                    item.entries.map((e) => '${e.key}: ${e.value}').join(' • '),
              )
              .toList(growable: false),
        ),
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: sections.isEmpty
          ? [Text(l10n.get('noAdditionalDetails'))]
          : sections,
    );
  }
}

class _Section extends StatelessWidget {
  const _Section({required this.title, required this.lines});

  final String title;
  final List<String> lines;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            ...lines.map(
              (line) => Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text(line),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
