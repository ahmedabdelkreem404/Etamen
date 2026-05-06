import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/account/presentation/widgets/settings_tile.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class SettingsPage extends StatelessWidget {
  const SettingsPage({super.key});

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return AppScaffold(
      title: l10n.get('settings'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          SettingsTile(
            icon: Icons.language,
            title: l10n.get('language'),
            onTap: () => context.push(RouteNames.languageSettings),
          ),
          SettingsTile(
            icon: Icons.notifications_none,
            title: l10n.get('notificationPreferences'),
            onTap: () => context.push(RouteNames.notificationPreferences),
          ),
          const SizedBox(height: 12),
          Text(
            l10n.get('legalAndPrivacy'),
            style: Theme.of(
              context,
            ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w700),
          ),
          const SizedBox(height: 8),
          SettingsTile(
            icon: Icons.privacy_tip_outlined,
            title: l10n.get('privacyPolicy'),
            onTap: () => context.push(RouteNames.legalPrivacy),
          ),
          SettingsTile(
            icon: Icons.description_outlined,
            title: l10n.get('termsConditions'),
            onTap: () => context.push(RouteNames.legalTerms),
          ),
          SettingsTile(
            icon: Icons.medical_information_outlined,
            title: l10n.get('medicalDisclaimer'),
            onTap: () => context.push(RouteNames.legalMedicalDisclaimer),
          ),
          SettingsTile(
            icon: Icons.smart_toy_outlined,
            title: l10n.get('aiDisclaimer'),
            onTap: () => context.push(RouteNames.legalAiDisclaimer),
          ),
          SettingsTile(
            icon: Icons.payments_outlined,
            title: l10n.get('refundCancellationPolicy'),
            onTap: () => context.push(RouteNames.legalRefundPolicy),
          ),
          const SizedBox(height: 12),
          SettingsTile(
            icon: Icons.support_agent,
            title: l10n.get('supportHelp'),
            onTap: () => context.push(RouteNames.support),
          ),
          SettingsTile(
            icon: Icons.info_outline,
            title: l10n.get('aboutApp'),
            onTap: () => context.push(RouteNames.about),
          ),
        ],
      ),
    );
  }
}
