import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/domain/entities/notification_preference.dart';
import 'package:etamen_app/features/notifications/presentation/providers/notifications_providers.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_preferences_section.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class NotificationPreferencesPage extends ConsumerWidget {
  const NotificationPreferencesPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(notificationPreferencesControllerProvider);
    final controller = ref.read(
      notificationPreferencesControllerProvider.notifier,
    );

    return AppScaffold(
      title: l10n.get('notificationPreferences'),
      body: state.isLoading && state.preferences.isEmpty
          ? const LoadingView()
          : state.error != null && state.preferences.isEmpty
          ? ErrorView(
              message: state.error!.message,
              onRetry: () => controller.load(),
            )
          : RefreshIndicator(
              onRefresh: controller.load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  if (state.preferences.isEmpty)
                    EmptyView(message: l10n.get('noNotificationPreferences'))
                  else
                    ..._groupByCategory(state.preferences).entries.map(
                      (entry) => NotificationPreferencesSection(
                        category: entry.key,
                        preferences: entry.value,
                        onToggle: controller.toggle,
                      ),
                    ),
                  const SizedBox(height: 16),
                  AppButton(
                    label: l10n.get('savePreferences'),
                    isLoading: state.isSubmitting,
                    onPressed: state.preferences.isEmpty
                        ? null
                        : () async {
                            final ok = await controller.save();
                            if (ok && context.mounted) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(l10n.get('preferencesSaved')),
                                ),
                              );
                            }
                          },
                  ),
                ],
              ),
            ),
    );
  }

  Map<NotificationCategory, List<NotificationPreference>> _groupByCategory(
    List<NotificationPreference> preferences,
  ) {
    final grouped = <NotificationCategory, List<NotificationPreference>>{};
    for (final preference in preferences) {
      grouped.putIfAbsent(preference.category, () => []).add(preference);
    }
    return grouped;
  }
}
