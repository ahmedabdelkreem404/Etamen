import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/localization/locale_provider.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class LanguageSettingsPage extends ConsumerWidget {
  const LanguageSettingsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final locale = ref.watch(localeControllerProvider);

    return AppScaffold(
      title: l10n.get('language'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: RadioListTile<String>(
              value: 'ar',
              groupValue: locale.languageCode,
              title: Text(l10n.get('arabic')),
              subtitle: Text(l10n.get('arabicRtl')),
              onChanged: (_) => _setLocale(context, ref, const Locale('ar')),
            ),
          ),
          Card(
            child: RadioListTile<String>(
              value: 'en',
              groupValue: locale.languageCode,
              title: Text(l10n.get('english')),
              subtitle: Text(l10n.get('englishLtr')),
              onChanged: (_) => _setLocale(context, ref, const Locale('en')),
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _setLocale(
    BuildContext context,
    WidgetRef ref,
    Locale locale,
  ) async {
    final l10n = AppLocalizations.of(context);
    await ref.read(localeControllerProvider.notifier).setLocale(locale);
    if (!context.mounted) return;
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(l10n.get('languageSaved'))));
  }
}
