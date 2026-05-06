import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/medications/presentation/providers/medications_providers.dart';
import 'package:etamen_app/features/medications/presentation/widgets/adherence_summary_card.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_disclaimer_box.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class MedicationAdherencePage extends ConsumerWidget {
  const MedicationAdherencePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(medicationAdherenceControllerProvider);
    final controller = ref.read(medicationAdherenceControllerProvider.notifier);
    return AppScaffold(
      title: l10n.get('adherence'),
      body: RefreshIndicator(
        onRefresh: controller.load,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          children: [
            const MedicationDisclaimerBox(),
            const SizedBox(height: 16),
            if (state.isLoading)
              const LoadingView()
            else if (state.error != null)
              ErrorView(message: state.error!.message, onRetry: controller.load)
            else if (state.adherence != null)
              AdherenceSummaryCard(adherence: state.adherence!)
            else
              Text(l10n.get('noAdherenceYet')),
          ],
        ),
      ),
    );
  }
}
