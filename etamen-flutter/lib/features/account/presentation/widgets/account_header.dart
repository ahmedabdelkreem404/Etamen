import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/features/auth/domain/entities/auth_user.dart';
import 'package:flutter/material.dart';

class AccountHeader extends StatelessWidget {
  const AccountHeader({required this.user, super.key});

  final AuthUser? user;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final displayName = user?.name?.trim().isNotEmpty == true
        ? user!.name!
        : user?.email ?? l10n.get('account');
    final roles = user?.roles.join(', ');

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          children: [
            CircleAvatar(
              radius: 28,
              child: Text(
                displayName.substring(0, 1).toUpperCase(),
                style: Theme.of(context).textTheme.titleLarge,
              ),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    displayName,
                    style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  if (user?.email.isNotEmpty == true) ...[
                    const SizedBox(height: 4),
                    Text(user!.email),
                  ],
                  if (roles != null && roles.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    Text('${l10n.get('role')}: $roles'),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
