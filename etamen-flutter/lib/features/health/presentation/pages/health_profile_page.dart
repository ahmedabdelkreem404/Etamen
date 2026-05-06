import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/health/domain/entities/health_profile.dart';
import 'package:etamen_app/features/health/presentation/providers/health_providers.dart';
import 'package:etamen_app/features/health/presentation/widgets/health_disclaimer_box.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class HealthProfilePage extends ConsumerWidget {
  const HealthProfilePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(healthProfileControllerProvider);
    final controller = ref.read(healthProfileControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('healthProfile'),
      actions: [
        IconButton(
          tooltip: l10n.get('editProfile'),
          onPressed: () => context.push(RouteNames.editHealthProfile),
          icon: const Icon(Icons.edit_outlined),
        ),
      ],
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            const HealthDisclaimerBox(),
            const SizedBox(height: 16),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else
              _ProfileContent(profile: state.profile),
          ],
        ),
      ),
    );
  }
}

class _ProfileContent extends StatelessWidget {
  const _ProfileContent({required this.profile});

  final HealthProfile? profile;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final current = profile ?? const HealthProfile();
    return Column(
      children: [
        _InfoCard(
          title: l10n.get('basicInfo'),
          rows: [
            _InfoRow(l10n.get('birthDate'), current.birthDate ?? '-'),
            _InfoRow(l10n.get('gender'), current.gender ?? '-'),
            _InfoRow(l10n.get('heightCm'), current.heightCm ?? '-'),
            _InfoRow(l10n.get('weightKg'), current.weightKg ?? '-'),
            _InfoRow(l10n.get('bloodType'), current.bloodType ?? '-'),
          ],
        ),
        _ListCard(title: l10n.get('chronicDiseases'), items: current.chronicDiseases),
        _ListCard(title: l10n.get('allergies'), items: current.allergies),
        _ListCard(
          title: l10n.get('currentMedications'),
          items: current.currentMedications,
        ),
        _ListCard(title: l10n.get('surgeries'), items: current.surgeries),
        _ListCard(title: l10n.get('goals'), items: current.goals),
      ],
    );
  }
}

class _InfoCard extends StatelessWidget {
  const _InfoCard({required this.title, required this.rows});

  final String title;
  final List<_InfoRow> rows;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            ...rows.map(
              (row) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 5),
                child: Row(
                  children: [
                    Expanded(child: Text(row.label)),
                    Text(row.value),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ListCard extends StatelessWidget {
  const _ListCard({required this.title, required this.items});

  final String title;
  final List<String> items;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            if (items.isEmpty)
              const Text('-')
            else
              ...items.map((item) => Text('- $item')),
          ],
        ),
      ),
    );
  }
}

class _InfoRow {
  const _InfoRow(this.label, this.value);

  final String label;
  final String value;
}
