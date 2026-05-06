import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/localization/locale_provider.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class AccountPage extends ConsumerWidget {
  const AccountPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final authState = ref.watch(authControllerProvider);
    final user = authState.user;

    return AppScaffold(
      title: l10n.get('account'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            child: ListTile(
              leading: const CircleAvatar(child: Icon(Icons.person)),
              title: Text(user?.name ?? user?.email ?? ''),
              subtitle: Text(user?.roles.join(', ') ?? ''),
            ),
          ),
          const SizedBox(height: 12),
          Card(
            child: SwitchListTile(
              value: AppLocalizations.of(context).isArabic,
              onChanged: (_) =>
                  ref.read(localeControllerProvider.notifier).toggle(),
              title: Text(l10n.get('language')),
              subtitle: Text(
                AppLocalizations.of(context).isArabic
                    ? l10n.get('arabic')
                    : l10n.get('english'),
              ),
            ),
          ),
          const SizedBox(height: 12),
          Card(
            child: ListTile(
              leading: const Icon(Icons.notifications_none),
              title: Text(l10n.get('notificationPreferences')),
              trailing: const Icon(Icons.chevron_right),
              onTap: () => context.push(RouteNames.notificationPreferences),
            ),
          ),
          const SizedBox(height: 12),
          OutlinedButton.icon(
            onPressed: () => ref.read(authControllerProvider.notifier).logout(),
            icon: const Icon(Icons.logout),
            label: Text(l10n.get('logout')),
          ),
        ],
      ),
    );
  }
}
