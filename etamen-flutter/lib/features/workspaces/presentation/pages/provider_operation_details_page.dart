import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/workspaces/data/models/provider_operation_models.dart';
import 'package:etamen_app/features/workspaces/presentation/pages/provider_operation_sections.dart';
import 'package:etamen_app/features/workspaces/presentation/providers/provider_operation_providers.dart';
import 'package:etamen_app/features/workspaces/presentation/providers/workspace_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class ProviderOperationDetailsPage extends ConsumerWidget {
  const ProviderOperationDetailsPage({
    required this.providerId,
    required this.section,
    required this.itemId,
    super.key,
  });

  final int providerId;
  final String section;
  final int itemId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final config = providerOperationSection(section);
    final isArabic = AppLocalizations.of(context).isArabic;
    final args = ProviderOperationItemArgs(
      providerId: providerId,
      section: config.section,
      itemId: itemId,
    );
    final state = ref.watch(providerOperationDetailsControllerProvider(args));
    final controller = ref.read(
      providerOperationDetailsControllerProvider(args).notifier,
    );
    final dashboardState = ref.watch(
      providerDashboardControllerProvider(providerId),
    );
    final permissions =
        dashboardState.dashboard?.permissions ?? const <String>[];

    return AppScaffold(
      title: config.title(isArabic),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            if (state.isLoading && state.item == null)
              const LoadingView()
            else if (state.error != null && state.item == null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.item != null) ...[
              _Header(item: state.item!),
              const SizedBox(height: 12),
              if (state.error != null)
                _InlineError(message: state.error!.message),
              _DetailsTable(raw: state.item!.raw),
              const SizedBox(height: 12),
              _Actions(
                config: config,
                item: state.item!,
                permissions: permissions,
                isSubmitting: state.isSubmitting,
                onAction: (action, reason) async {
                  final ok = await controller.runAction(
                    action.key,
                    reason: reason,
                  );
                  if (!context.mounted) return;
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text(
                        ok
                            ? (isArabic
                                  ? 'تم تنفيذ الإجراء من السيرفر.'
                                  : 'Action completed by the server.')
                            : (state.error?.message ??
                                  (isArabic
                                      ? 'تعذر تنفيذ الإجراء.'
                                      : 'Action failed.')),
                      ),
                    ),
                  );
                },
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({required this.item});

  final ProviderOperationItem item;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              item.title(isArabic),
              style: Theme.of(
                context,
              ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 8),
            Text(item.subtitle(isArabic)),
            if (item.amountLabel(isArabic) != null) ...[
              const SizedBox(height: 10),
              Chip(
                avatar: const Icon(Icons.payments_outlined, size: 18),
                label: Text(item.amountLabel(isArabic)!),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _DetailsTable extends StatelessWidget {
  const _DetailsTable({required this.raw});

  final Map<String, dynamic> raw;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    final rows = _flatten(raw).entries
        .where((entry) => !_hiddenKey(entry.key))
        .take(18)
        .toList(growable: false);

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              isArabic ? 'تفاصيل مختصرة' : 'Safe details',
              style: Theme.of(
                context,
              ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 8),
            for (final row in rows)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 5),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: 126,
                      child: Text(
                        _label(row.key, isArabic),
                        style: const TextStyle(fontWeight: FontWeight.w700),
                      ),
                    ),
                    const SizedBox(width: 8),
                    Expanded(child: Text(_formatValue(row.value, isArabic))),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _Actions extends StatelessWidget {
  const _Actions({
    required this.config,
    required this.item,
    required this.permissions,
    required this.isSubmitting,
    required this.onAction,
  });

  final ProviderOperationSection config;
  final ProviderOperationItem item;
  final List<String> permissions;
  final bool isSubmitting;
  final Future<void> Function(ProviderOperationAction action, String? reason)
  onAction;

  @override
  Widget build(BuildContext context) {
    final actions = _validActionsForItem(config.section, item, config.actions);
    if (actions.isEmpty) return const SizedBox.shrink();
    final isArabic = AppLocalizations.of(context).isArabic;
    final canManage =
        config.managePermission != null &&
        permissions.contains(config.managePermission);

    if (!canManage) {
      return Card(
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              const Icon(Icons.lock_outline, color: Colors.orange),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  isArabic
                      ? 'ليست لديك صلاحية تعديل حالة هذا السجل.'
                      : 'You do not have permission to change this record.',
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Text(
              isArabic ? 'إجراءات الحالة' : 'Status actions',
              style: Theme.of(
                context,
              ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 10),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                for (final action in actions)
                  FilledButton.tonalIcon(
                    style: action.destructive
                        ? FilledButton.styleFrom(
                            foregroundColor: Theme.of(
                              context,
                            ).colorScheme.error,
                          )
                        : null,
                    onPressed: isSubmitting
                        ? null
                        : () async {
                            String? reason;
                            if (action.requiresReason) {
                              reason = await _askReason(context, action);
                              if (reason == null || reason.trim().isEmpty) {
                                return;
                              }
                            }
                            await onAction(action, reason);
                          },
                    icon: isSubmitting
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : Icon(
                            action.destructive
                                ? Icons.cancel_outlined
                                : Icons.check_circle_outline,
                          ),
                    label: Text(action.label(isArabic)),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Future<String?> _askReason(
    BuildContext context,
    ProviderOperationAction action,
  ) async {
    final isArabic = AppLocalizations.of(context).isArabic;
    final controller = TextEditingController();
    final result = await showDialog<String>(
      context: context,
      builder: (context) {
        return AlertDialog(
          title: Text(action.label(isArabic)),
          content: TextField(
            controller: controller,
            maxLines: 3,
            decoration: InputDecoration(
              labelText: isArabic ? 'سبب الإجراء' : 'Reason',
              hintText: isArabic
                  ? 'اكتب سببًا واضحًا قبل التنفيذ'
                  : 'Add a clear reason before continuing',
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: Text(isArabic ? 'إلغاء' : 'Cancel'),
            ),
            FilledButton(
              onPressed: () =>
                  Navigator.of(context).pop(controller.text.trim()),
              child: Text(isArabic ? 'تنفيذ' : 'Continue'),
            ),
          ],
        );
      },
    );
    controller.dispose();
    return result;
  }
}

List<ProviderOperationAction> _validActionsForItem(
  String section,
  ProviderOperationItem item,
  List<ProviderOperationAction> actions,
) {
  final status = item.status;
  if (status == null) return actions;

  final keys = switch (section) {
    'pharmacy/orders' => switch (status) {
      'pending' || 'pharmacy_review' => const ['accept', 'reject'],
      'accepted' || 'awaiting_payment' => const ['reject'],
      'paid' => const ['preparing', 'ready', 'out-for-delivery', 'complete'],
      'preparing' => const ['ready', 'out-for-delivery', 'complete'],
      'ready' || 'ready_for_pickup' => const ['out-for-delivery', 'complete'],
      'out_for_delivery' => const ['complete'],
      _ => const <String>[],
    },
    'lab/orders' => switch (status) {
      'lab_review' => const ['accept', 'reject'],
      'accepted' || 'awaiting_payment' => const ['reject'],
      'paid' => const [
        'sample-scheduled',
        'sample-collected',
        'processing',
        'result-ready',
      ],
      'sample_scheduled' => const ['sample-collected', 'processing'],
      'sample_collected' => const ['processing', 'result-ready'],
      'processing' => const ['result-ready'],
      'result_ready' => const ['complete'],
      _ => const <String>[],
    },
    _ => null,
  };

  if (keys == null) return actions;
  return actions.where((action) => keys.contains(action.key)).toList();
}

class _InlineError extends StatelessWidget {
  const _InlineError({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Card(
      color: Colors.red.shade50,
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: [
            Icon(Icons.error_outline, color: Colors.red.shade700),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                message,
                style: TextStyle(color: Colors.red.shade800),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

Map<String, Object?> _flatten(Map<String, dynamic> raw) {
  final values = <String, Object?>{};
  for (final entry in raw.entries) {
    final value = entry.value;
    if (value == null) continue;
    if (value is Map) {
      for (final nested in value.entries) {
        if (nested.value == null) continue;
        values['${entry.key}.${nested.key}'] = nested.value;
      }
    } else if (value is! List) {
      values[entry.key] = value;
    }
  }
  return values;
}

bool _hiddenKey(String key) {
  final lower = key.toLowerCase();
  return lower.contains('path') ||
      lower.contains('config') ||
      lower.contains('secret') ||
      lower.contains('token') ||
      lower.contains('notes') && lower.contains('admin') ||
      lower.startsWith('status_label_') ||
      lower.startsWith('payment_status_label_') ||
      lower.startsWith('can_') ||
      lower.startsWith('next_action_') ||
      lower.endsWith('_id') ||
      lower == 'patient.id' ||
      lower == 'provider.id' ||
      lower == 'pharmacy.id' ||
      lower == 'lab.id';
}

String _label(String key, bool isArabic) {
  final labels = {
    'id': isArabic ? 'المعرّف' : 'ID',
    'number': isArabic ? 'الرقم' : 'Number',
    'status': isArabic ? 'الحالة' : 'Status',
    'payment_status': isArabic ? 'حالة الدفع' : 'Payment status',
    'payment.status': isArabic ? 'حالة الدفع' : 'Payment status',
    'patient.name': isArabic ? 'اسم العميل' : 'Patient',
    'total_amount': isArabic ? 'الإجمالي' : 'Total',
    'grand_total': isArabic ? 'الإجمالي' : 'Total',
    'price': isArabic ? 'السعر' : 'Price',
    'scheduled_at': isArabic ? 'الموعد' : 'Scheduled at',
    'booked_at': isArabic ? 'وقت الحجز' : 'Booked at',
  };
  return labels[key] ?? key.replaceAll('_', ' ').replaceAll('.', ' ');
}

String _formatValue(Object? value, bool isArabic) {
  if (value == null) return '-';
  if (value is bool) {
    if (!isArabic) return value ? 'Yes' : 'No';
    return value ? 'نعم' : 'لا';
  }
  final text = value.toString();
  if (text.trim().isEmpty) return '-';
  final friendly = friendlyStatus(text, isArabic);
  if (friendly != text || text.contains('_')) return friendly;
  return text;
}
