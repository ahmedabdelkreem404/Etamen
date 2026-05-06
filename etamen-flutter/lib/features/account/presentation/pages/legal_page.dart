import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/legal/legal_document_type.dart';
import 'package:etamen_app/core/legal/legal_documents.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/account/presentation/widgets/legal_section.dart';
import 'package:flutter/material.dart';

class LegalPage extends StatelessWidget {
  const LegalPage({required this.type, super.key});

  final LegalDocumentType type;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final document = LegalDocuments.byType(type);
    final sections = document.sections(l10n.locale.languageCode);

    return AppScaffold(
      title: document.title(l10n.locale.languageCode),
      body: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemBuilder: (context, index) {
          return LegalSection(text: sections[index]);
        },
        separatorBuilder: (context, index) => const SizedBox(height: 8),
        itemCount: sections.length,
      ),
    );
  }
}
