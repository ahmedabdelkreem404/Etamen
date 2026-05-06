import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/notifications/domain/entities/app_notification.dart';
import 'package:etamen_app/features/notifications/presentation/providers/notifications_providers.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_category_chip.dart';
import 'package:etamen_app/features/notifications/presentation/widgets/notification_priority_chip.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class NotificationDetailsPage extends ConsumerWidget {
  const NotificationDetailsPage({required this.notificationId, super.key});

  final int notificationId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(
      notificationDetailsControllerProvider(notificationId),
    );
    final controller = ref.read(
      notificationDetailsControllerProvider(notificationId).notifier,
    );
    final notification = state.notification;

    return AppScaffold(
      title: l10n.get('notificationDetails'),
      actions: [
        IconButton(
          tooltip: l10n.get('deleteNotification'),
          onPressed: notification == null
              ? null
              : () async {
                  final ok = await controller.delete();
                  if (ok && context.mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(content: Text(l10n.get('notificationDeleted'))),
                    );
                    context.pop();
                  }
                },
          icon: const Icon(Icons.delete_outline),
        ),
      ],
      body: state.isLoading && notification == null
          ? const LoadingView()
          : state.error != null && notification == null
          ? ErrorView(
              message: state.error!.message,
              onRetry: () => controller.load(),
            )
          : notification == null
          ? ErrorView(message: l10n.get('notificationNotFound'))
          : RefreshIndicator(
              onRefresh: controller.load,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: [
                              NotificationCategoryChip(
                                category: notification.category,
                              ),
                              NotificationPriorityChip(
                                priority: notification.priority,
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          Text(
                            notification.title,
                            style: Theme.of(context).textTheme.titleLarge,
                          ),
                          const SizedBox(height: 8),
                          Text(notification.body),
                          const SizedBox(height: 12),
                          Text(
                            notification.createdAt?.toLocal().toString() ?? '-',
                            style: const TextStyle(color: AppColors.muted),
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  if (notification.data.isNotEmpty)
                    _SafeDataCard(data: notification.data),
                  const SizedBox(height: 16),
                  if (!notification.isRead)
                    AppButton(
                      label: l10n.get('read'),
                      isLoading: state.isSubmitting,
                      onPressed: () async {
                        final ok = await controller.markRead();
                        if (ok && context.mounted) {
                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Text(l10n.get('notificationMarkedRead')),
                            ),
                          );
                        }
                      },
                    ),
                  if (_routeFor(notification) != null) ...[
                    const SizedBox(height: 12),
                    AppButton(
                      label: l10n.get('viewDetails'),
                      onPressed: () => context.push(_routeFor(notification)!),
                    ),
                  ],
                ],
              ),
            ),
    );
  }
}

class _SafeDataCard extends StatelessWidget {
  const _SafeDataCard({required this.data});

  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final entries = data.entries
        .where((entry) => _isScalar(entry.value))
        .toList(growable: false);
    if (entries.isEmpty) return const SizedBox.shrink();

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              l10n.get('notificationData'),
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 8),
            ...entries.map(
              (entry) => Padding(
                padding: const EdgeInsets.only(top: 6),
                child: Row(
                  children: [
                    Expanded(child: Text(entry.key)),
                    const SizedBox(width: 12),
                    Flexible(
                      child: Text(
                        '${entry.value}',
                        textAlign: TextAlign.end,
                        style: const TextStyle(fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  bool _isScalar(Object? value) {
    return value == null || value is num || value is bool || value is String;
  }
}

String? _routeFor(AppNotification notification) {
  final data = notification.data;
  final appointmentId = _int(data['appointment_id']);
  if (appointmentId != null) {
    return RouteNames.appointmentDetails(appointmentId);
  }

  final pharmacyOrderId = _int(data['pharmacy_order_id']);
  if (pharmacyOrderId != null) {
    return RouteNames.pharmacyOrderDetails(pharmacyOrderId);
  }

  final labOrderId = _int(data['lab_order_id']);
  if (labOrderId != null) return RouteNames.labOrderDetails(labOrderId);

  final reminderId = _int(data['reminder_id']) ?? _int(data['medication_id']);
  if (reminderId != null) {
    return RouteNames.medicationReminderDetails(reminderId);
  }

  final carePlanId = _int(data['care_plan_id']);
  if (carePlanId != null) return RouteNames.carePlanDetails(carePlanId);

  final paymentId = _int(data['payment_id']);
  if (paymentId != null) return RouteNames.paymentStatus(paymentId);

  return null;
}

int? _int(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}
