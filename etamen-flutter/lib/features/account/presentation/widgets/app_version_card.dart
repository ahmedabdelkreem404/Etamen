import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/config/app_config.dart';
import 'package:flutter/material.dart';

class AppVersionCard extends StatelessWidget {
  const AppVersionCard({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final showEnvironment = AppConfig.environment != 'production';

    return Card(
      child: ListTile(
        leading: const Icon(Icons.info_outline),
        title: Text(l10n.get('appVersion')),
        subtitle: Text(
          showEnvironment
              ? '${AppConfig.appVersion}+${AppConfig.buildNumber} • ${AppConfig.environment}'
              : '${AppConfig.appVersion}+${AppConfig.buildNumber}',
        ),
      ),
    );
  }
}
