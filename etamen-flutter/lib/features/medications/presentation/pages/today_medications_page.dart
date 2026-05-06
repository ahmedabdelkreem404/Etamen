import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/medications/presentation/providers/medications_providers.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_disclaimer_box.dart';
import 'package:etamen_app/features/medications/presentation/widgets/today_medication_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class TodayMedicationsPage extends ConsumerWidget {
  const TodayMedicationsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(todayMedicationsControllerProvider);
    final controller = ref.read(todayMedicationsControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('todayDoses'),
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
            else if (state.isEmpty)
              EmptyView(
                message: l10n.get('noTodayMedications'),
                icon: Icons.today_outlined,
              )
            else
              ...state.items.map(
                (item) => TodayMedicationCard(
                  item: item,
                  isBusy: state.isSubmitting,
                  onTaken: () async {
                    final ok = await controller.markTaken(item);
                    if (!context.mounted) return;
                    if (ok) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text(l10n.get('doseTakenSaved'))),
                      );
                    }
                  },
                  onSkipped: () async {
                    final ok = await controller.markSkipped(item);
                    if (!context.mounted) return;
                    if (ok) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(content: Text(l10n.get('doseSkippedSaved'))),
                      );
                    }
                  },
                ),
              ),
          ],
        ),
      ),
    );
  }
}
