import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/config/app_config.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/account/presentation/widgets/app_version_card.dart';
import 'package:etamen_app/features/account/presentation/widgets/settings_tile.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class AboutPage extends StatelessWidget {
  const AboutPage({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final showEnvironment = AppConfig.environment != 'production';

    return AppScaffold(
      title: l10n.get('aboutApp'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    '${AppConfig.appName} / اطمن',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(l10n.get('aboutAppDescription')),
                  if (showEnvironment) ...[
                    const SizedBox(height: 8),
                    Text(
                      '${l10n.get('environment')}: ${AppConfig.environment}',
                    ),
                  ],
                ],
              ),
            ),
          ),
          const AppVersionCard(),
          SettingsTile(
            icon: Icons.privacy_tip_outlined,
            title: l10n.get('privacyPolicy'),
            onTap: () => context.push(RouteNames.legalPrivacy),
          ),
          SettingsTile(
            icon: Icons.support_agent,
            title: l10n.get('supportHelp'),
            onTap: () => context.push(RouteNames.support),
          ),
        ],
      ),
    );
  }
}
