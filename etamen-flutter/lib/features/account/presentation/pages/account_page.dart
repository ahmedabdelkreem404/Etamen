import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/account/presentation/widgets/account_header.dart';
import 'package:etamen_app/features/account/presentation/widgets/app_version_card.dart';
import 'package:etamen_app/features/account/presentation/widgets/logout_button.dart';
import 'package:etamen_app/features/account/presentation/widgets/settings_tile.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class AccountPage extends ConsumerWidget {
  const AccountPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;

    final body = ListView(
      padding: const EdgeInsets.all(16),
      children: [
        AccountHeader(user: user),
        const AppVersionCard(),
        const SizedBox(height: 12),
        SettingsTile(
          icon: Icons.settings_outlined,
          title: l10n.get('settings'),
          onTap: () => context.push(RouteNames.accountSettings),
        ),
        SettingsTile(
          icon: Icons.language,
          title: l10n.get('language'),
          subtitle: AppLocalizations.of(context).isArabic
              ? l10n.get('arabic')
              : l10n.get('english'),
          onTap: () => context.push(RouteNames.languageSettings),
        ),
        SettingsTile(
          icon: Icons.notifications_none,
          title: l10n.get('notificationPreferences'),
          onTap: () => context.push(RouteNames.notificationPreferences),
        ),
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
        const SizedBox(height: 12),
        const LogoutButton(),
      ],
    );

    if (!showAppBar) return body;

    return AppScaffold(title: l10n.get('account'), body: body);
  }
}
