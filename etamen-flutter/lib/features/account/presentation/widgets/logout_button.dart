import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class LogoutButton extends ConsumerWidget {
  const LogoutButton({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final isLoading = ref.watch(authControllerProvider).isLoading;

    return OutlinedButton.icon(
      onPressed: isLoading
          ? null
          : () async {
              final confirmed = await showDialog<bool>(
                context: context,
                builder: (dialogContext) {
                  return AlertDialog(
                    title: Text(l10n.get('logout')),
                    content: Text(l10n.get('confirmLogout')),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.of(dialogContext).pop(false),
                        child: Text(l10n.get('back')),
                      ),
                      FilledButton(
                        onPressed: () => Navigator.of(dialogContext).pop(true),
                        child: Text(l10n.get('logout')),
                      ),
                    ],
                  );
                },
              );
              if (confirmed != true) return;
              final cleanLogout = await ref
                  .read(authControllerProvider.notifier)
                  .logout();
              if (!cleanLogout && context.mounted) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(l10n.get('logoutLocalFallback'))),
                );
              }
            },
      icon: const Icon(Icons.logout),
      label: Text(l10n.get('logout')),
    );
  }
}
