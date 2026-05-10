import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/account/presentation/widgets/logout_button.dart';
import 'package:etamen_app/features/workspaces/data/models/workspace_models.dart';
import 'package:etamen_app/features/workspaces/presentation/pages/provider_operation_sections.dart';
import 'package:etamen_app/features/workspaces/presentation/providers/workspace_providers.dart';
import 'package:etamen_app/features/workspaces/presentation/widgets/workspace_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class ProviderDashboardPage extends ConsumerWidget {
  const ProviderDashboardPage({required this.providerId, super.key});

  final int providerId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final state = ref.watch(providerDashboardControllerProvider(providerId));

    return AppScaffold(
      title: 'لوحة المزود',
      actions: [
        IconButton(
          tooltip: 'تبديل مساحة العمل',
          onPressed: () => showWorkspaceSwitcher(context, ref),
          icon: const Icon(Icons.swap_horiz),
        ),
        IconButton(
          tooltip: 'حسابي',
          onPressed: () => context.push(RouteNames.account),
          icon: const Icon(Icons.person_outline),
        ),
      ],
      body: RefreshIndicator(
        onRefresh: () => ref
            .read(providerDashboardControllerProvider(providerId).notifier)
            .load(),
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            if (state.isLoading && state.dashboard == null)
              const Padding(
                padding: EdgeInsets.only(top: 60),
                child: Center(child: CircularProgressIndicator()),
              )
            else if (state.error != null && state.dashboard == null)
              _ErrorState(
                message: state.error!.message,
                onRetry: () => ref
                    .read(
                      providerDashboardControllerProvider(providerId).notifier,
                    )
                    .load(),
              )
            else if (state.dashboard != null)
              _DashboardBody(dashboard: state.dashboard!),
          ],
        ),
      ),
    );
  }
}

class _DashboardBody extends StatelessWidget {
  const _DashboardBody({required this.dashboard});

  final ProviderDashboard dashboard;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        _HeaderCard(dashboard: dashboard),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: [
            for (final permission in dashboard.permissions.take(6))
              Chip(
                label: Text(permission.replaceAll('_', ' ')),
                visualDensity: VisualDensity.compact,
              ),
          ],
        ),
        const SizedBox(height: 12),
        _CountStrip(dashboard: dashboard),
        const SizedBox(height: 12),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: dashboard.summaryCards.length,
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            childAspectRatio: 1.65,
            crossAxisSpacing: 10,
            mainAxisSpacing: 10,
          ),
          itemBuilder: (context, index) {
            final card = dashboard.summaryCards[index];
            return _SummaryCard(card: card);
          },
        ),
        const SizedBox(height: 18),
        Text(
          'إجراءات سريعة',
          style: Theme.of(
            context,
          ).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w900),
        ),
        const SizedBox(height: 8),
        for (final action in dashboard.quickActions)
          Card(
            child: ListTile(
              leading: const Icon(Icons.playlist_add_check_circle_outlined),
              title: Text(action.label(isArabic)),
              subtitle: const Text('سيتم تفعيلها في مرحلة تشغيل لوحة المزود'),
              onTap: () {
                final section = operationSectionForQuickAction(
                  dashboard.provider.type,
                  action.key,
                );
                if (section != null) {
                  context.push(
                    RouteNames.providerOperation(
                      dashboard.provider.id,
                      section.section,
                    ),
                  );
                  return;
                }
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('سيتم تفعيلها في مرحلة تشغيل لوحة المزود'),
                  ),
                );
              },
            ),
          ),
        if (dashboard.quickActions.isEmpty)
          const Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Text('لا توجد إجراءات متاحة لهذا الدور حاليًا.'),
            ),
          ),
        const SizedBox(height: 14),
        FilledButton.icon(
          onPressed: () => context.go(RouteNames.home),
          icon: const Icon(Icons.person_outline),
          label: const Text('الرجوع لمساحة المريض'),
        ),
        const SizedBox(height: 8),
        const LogoutButton(),
      ],
    );
  }
}

class _HeaderCard extends StatelessWidget {
  const _HeaderCard({required this.dashboard});

  final ProviderDashboard dashboard;

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
              dashboard.provider.name(isArabic),
              style: Theme.of(
                context,
              ).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w900),
            ),
            const SizedBox(height: 6),
            Text(
              '${_providerTypeLabel(dashboard.provider.type, isArabic)} - ${dashboard.role}',
              style: Theme.of(
                context,
              ).textTheme.bodyMedium?.copyWith(color: Colors.grey.shade700),
            ),
            if (dashboard.provider.primaryCityName != null ||
                dashboard.provider.primaryAreaName != null) ...[
              const SizedBox(height: 6),
              Text(
                [
                  dashboard.provider.primaryAreaName,
                  dashboard.provider.primaryCityName,
                ].whereType<String>().join(' - '),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _CountStrip extends StatelessWidget {
  const _CountStrip({required this.dashboard});

  final ProviderDashboard dashboard;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: _MiniCount(
            label: 'اليوم',
            value: dashboard.todayCount,
            icon: Icons.today_outlined,
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _MiniCount(
            label: 'مراجعة دفع',
            value: dashboard.pendingPaymentReviewCount,
            icon: Icons.payments_outlined,
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _MiniCount(
            label: 'إجراءات',
            value: dashboard.pendingActionsCount,
            icon: Icons.pending_actions_outlined,
          ),
        ),
      ],
    );
  }
}

class _MiniCount extends StatelessWidget {
  const _MiniCount({
    required this.label,
    required this.value,
    required this.icon,
  });

  final String label;
  final int value;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(10),
        child: Column(
          children: [
            Icon(icon, color: AppColors.primary),
            const SizedBox(height: 6),
            Text(
              value.toString(),
              style: Theme.of(
                context,
              ).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w900),
            ),
            Text(label, maxLines: 1, overflow: TextOverflow.ellipsis),
          ],
        ),
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  const _SummaryCard({required this.card});

  final DashboardCard card;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              card.value.toString(),
              style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                color: AppColors.primaryDark,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              card.label(isArabic),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}

class _ErrorState extends StatelessWidget {
  const _ErrorState({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          children: [
            const Icon(Icons.error_outline, color: Colors.red),
            const SizedBox(height: 8),
            Text(message, textAlign: TextAlign.center),
            const SizedBox(height: 12),
            FilledButton(
              onPressed: onRetry,
              child: const Text('إعادة المحاولة'),
            ),
          ],
        ),
      ),
    );
  }
}

String _providerTypeLabel(String value, bool isArabic) {
  if (!isArabic) return value.replaceAll('_', ' ');
  return switch (value) {
    'doctor' => 'طبيب',
    'hospital' => 'مستشفى',
    'radiology' => 'مركز أشعة',
    'pharmacy' => 'صيدلية',
    'lab' => 'معمل',
    'gym' => 'جيم',
    'fitness_coach' => 'كابتن جيم',
    'nutrition_coach' => 'كوتش تغذية',
    _ => value,
  };
}
