import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/care_plans/data/models/create_meal_log_request.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/presentation/providers/care_plans_providers.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_disclaimer_box.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/meal_log_form.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class MealLogPage extends ConsumerStatefulWidget {
  const MealLogPage({required this.planId, super.key});

  final int planId;

  @override
  ConsumerState<MealLogPage> createState() => _MealLogPageState();
}

class _MealLogPageState extends ConsumerState<MealLogPage> {
  int? _selectedMealId;
  MealType _mealType = MealType.breakfast;
  MealLogStatus _status = MealLogStatus.followed;
  late final TextEditingController _descriptionController;
  late final TextEditingController _notesController;

  @override
  void initState() {
    super.initState();
    _descriptionController = TextEditingController();
    _notesController = TextEditingController();
  }

  @override
  void dispose() {
    _descriptionController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final details = ref.watch(carePlanDetailsControllerProvider(widget.planId));
    final formState = ref.watch(mealLogControllerProvider);
    final formController = ref.read(mealLogControllerProvider.notifier);

    return AppScaffold(
      title: l10n.get('logMeal'),
      body: details.isLoading
          ? const LoadingView()
          : details.error != null
          ? ErrorView(message: details.error!.message)
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                CarePlanDisclaimerBox(text: l10n.get('mealLogDisclaimer')),
                const SizedBox(height: 16),
                if (!details.canTrack)
                  Card(
                    child: ListTile(
                      leading: const Icon(Icons.lock_outline),
                      title: Text(l10n.get('inactivePlanMessage')),
                    ),
                  )
                else ...[
                  MealLogForm(
                    meals: details.meals,
                    selectedMealId: _selectedMealId,
                    mealType: _mealType,
                    status: _status,
                    descriptionController: _descriptionController,
                    notesController: _notesController,
                    onMealChanged: (value) =>
                        setState(() => _selectedMealId = value),
                    onMealTypeChanged: (value) =>
                        setState(() => _mealType = value),
                    onStatusChanged: (value) => setState(() => _status = value),
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
                            final result = await formController.submit(
                              widget.planId,
                              CreateMealLogRequest(
                                loggedAt: DateTime.now(),
                                status: _status,
                                carePlanMealId: _selectedMealId,
                                mealType: _mealType,
                                description: _descriptionController.text,
                                notes: _notesController.text,
                              ),
                            );
                            if (!context.mounted) return;
                            if (result != null) {
                              ScaffoldMessenger.of(context).showSnackBar(
                                SnackBar(
                                  content: Text(l10n.get('mealLogSaved')),
                                ),
                              );
                              context.pop();
                            }
                          },
                    icon: const Icon(Icons.save_outlined),
                    label: Text(l10n.get('save')),
                  ),
                ],
              ],
            ),
    );
  }
}
