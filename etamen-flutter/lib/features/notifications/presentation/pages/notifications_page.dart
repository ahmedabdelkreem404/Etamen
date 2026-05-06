import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/notifications/presentation/providers/notifications_providers.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_card.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_empty_state.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class NotificationsPage extends ConsumerWidget {
  const NotificationsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(notificationsControllerProvider);
    final controller = ref.read(notificationsControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('notifications'),
      actions: [
        IconButton(
          tooltip: l10n.get('notificationPreferences'),
          onPressed: () => context.push(RouteNames.notificationPreferences),
          icon: const Icon(Icons.tune),
        ),
        IconButton(
          tooltip: l10n.get('markAllRead'),
          onPressed: state.items.any((item) => !item.isRead)
              ? () async {
                  final ok = await controller.markAllRead();
                  if (ok && context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(l10n.get('markAllRead'))),
                    );
                  }
                }
              : null,
          icon: const Icon(Icons.done_all),
        ),
      ],
      body: state.isLoading && state.items.isEmpty
          ? const LoadingView()
          : state.error != null && state.items.isEmpty
          ? ErrorView(
              message: state.error!.message,
              onRetry: () => controller.load(),
            )
          : RefreshIndicator(
              onRefresh: controller.load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  _FilterChips(
                    selected: state.filter,
                    onChanged: controller.selectFilter,
                  ),
                  const SizedBox(height: 12),
                  if (state.isEmpty)
                    NotificationEmptyState(
                      message: l10n.get('noNotificationsYet'),
                    )
                  else
                    ...state.filteredItems.map(
                      (notification) => Padding(
                        padding: const EdgeInsets.only(bottom: 8),
                        child: NotificationCard(
                          notification: notification,
                          onTap: () async {
                            if (!notification.isRead) {
                              await controller.markRead(notification.id);
                            }
                            if (context.mounted) {
                              context.push(
                                RouteNames.notificationDetails(notification.id),
                              );
                            }
                          },
                        ),
                      ),
                    ),
                ],
              ),
            ),
    );
  }
}

class _FilterChips extends StatelessWidget {
  const _FilterChips({required this.selected, required this.onChanged});

  final NotificationFilter selected;
  final ValueChanged<NotificationFilter> onChanged;

  @override
  Widget build(BuildContext context) {
    final filters = <NotificationFilter, String>{
      NotificationFilter.all: 'all',
      NotificationFilter.unread: 'unread',
      NotificationFilter.appointments: 'appointmentsCategory',
      NotificationFilter.payments: 'paymentsCategory',
      NotificationFilter.pharmacy: 'pharmacyCategory',
      NotificationFilter.labs: 'labsCategory',
      NotificationFilter.medications: 'medicationsCategory',
      NotificationFilter.carePlans: 'carePlansCategory',
      NotificationFilter.system: 'systemCategory',
    };
    final l10n = AppLocalizations.of(context);

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: filters.entries
            .map(
              (entry) => Padding(
                padding: const EdgeInsetsDirectional.only(end: 8),
                child: FilterChip(
                  selected: selected == entry.key,
                  label: Text(l10n.get(entry.value)),
                  onSelected: (_) => onChanged(entry.key),
                ),
              ),
            )
            .toList(growable: false),
      ),
    );
  }
}
