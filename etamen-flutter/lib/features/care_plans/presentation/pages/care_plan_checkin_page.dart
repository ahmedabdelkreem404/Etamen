import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_checkin_request.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/presentation/providers/care_plans_providers.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_disclaimer_box.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/checkin_form.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class CarePlanCheckinPage extends ConsumerStatefulWidget {
  const CarePlanCheckinPage({required this.planId, super.key});

  final int planId;

  @override
  ConsumerState<CarePlanCheckinPage> createState() =>
      _CarePlanCheckinPageState();
}

class _CarePlanCheckinPageState extends ConsumerState<CarePlanCheckinPage> {
  int _commitmentScore = 70;
  int _energyLevel = 3;
  int _hungerLevel = 3;
  int _sleepQuality = 3;
  CheckinMood _mood = CheckinMood.neutral;
  late final TextEditingController _symptomsController;
  late final TextEditingController _generalController;

  @override
  void initState() {
    super.initState();
    _symptomsController = TextEditingController();
    _generalController = TextEditingController();
  }

  @override
  void dispose() {
    _symptomsController.dispose();
    _generalController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final details = ref.watch(carePlanDetailsControllerProvider(widget.planId));
    final formState = ref.watch(carePlanCheckinControllerProvider);
    final formController = ref.read(carePlanCheckinControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('dailyCheckin'),
      body: details.isLoading
          ? const LoadingView()
          : details.error != null
          ? ErrorView(message: details.error!.message)
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                const CarePlanDisclaimerBox(),
                const SizedBox(height: 16),
                if (!details.canTrack)
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.lock_outline),
                      title: Text(l10n.get('inactivePlanMessage')),
                    ),
                  )
                else ...[
                  CheckinForm(
                    commitmentScore: _commitmentScore,
                    energyLevel: _energyLevel,
                    hungerLevel: _hungerLevel,
                    sleepQuality: _sleepQuality,
                    mood: _mood,
                    symptomsController: _symptomsController,
                    generalController: _generalController,
                    onCommitmentChanged: (value) =>
                        setState(() => _commitmentScore = value),
                    onEnergyChanged: (value) =>
                        setState(() => _energyLevel = value),
                    onHungerChanged: (value) =>
                        setState(() => _hungerLevel = value),
                    onSleepChanged: (value) =>
                        setState(() => _sleepQuality = value),
                    onMoodChanged: (value) => setState(() => _mood = value),
                  ),
                  if (formState.error != null) ...[
                    const SizedBox(height: 12),
                    Text(
                      formState.error!.message,
                      style: const TextStyle(color: Colors.red),
                    ),
                  ],
                  const SizedBox(height: 18),
                  FilledButton.icon(
                    onPressed: formState.isSubmitting
                        ? null
                        : () async {
                            final today = DateTime.now();
                            final date =
                                '${today.year.toString().padLeft(4, '0')}-${today.month.toString().padLeft(2, '0')}-${today.day.toString().padLeft(2, '0')}';
                            final result = await formController.submit(
                              widget.planId,
                              CreateCarePlanCheckinRequest(
                                checkinDate: date,
                                commitmentScore: _commitmentScore,
                                energyLevel: _energyLevel,
                                hungerLevel: _hungerLevel,
                                sleepQuality: _sleepQuality,
                                mood: _mood,
                                symptomsNotes: _symptomsController.text,
                                generalNotes: _generalController.text,
                              ),
                            );
                            if (!context.mounted) return;
                            if (result != null) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(l10n.get('checkinSaved')),
                                ),
                              );
                              context.pop();
                            }
                          },
                    icon: const Icon(Icons.check),
                    label: Text(l10n.get('save')),
                  ),
                ],
              ],
            ),
    );
  }
}
