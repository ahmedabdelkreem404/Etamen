import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_reminder_request.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/presentation/providers/medications_providers.dart';
import 'package:etamen_app/features/medications/presentation/widgets/frequency_selector.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_disclaimer_box.dart';
import 'package:etamen_app/features/medications/presentation/widgets/reminder_times_editor.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class CreateMedicationReminderPage extends ConsumerStatefulWidget {
  const CreateMedicationReminderPage({super.key});

  @override
  ConsumerState<CreateMedicationReminderPage> createState() =>
      _CreateMedicationReminderPageState();
}

class _CreateMedicationReminderPageState
    extends ConsumerState<CreateMedicationReminderPage> {
  final _nameController = TextEditingController();
  final _dosageController = TextEditingController();
  final _unitController = TextEditingController();
  final _instructionsController = TextEditingController();
  final _intervalController = TextEditingController();
  final _startDateController = TextEditingController(text: _todayDate());
  final _endDateController = TextEditingController();
  final _prescribedByController = TextEditingController();
  final _notesController = TextEditingController();
  final _refillQuantityController = TextEditingController();
  final _refillThresholdController = TextEditingController();
  final _refillDateController = TextEditingController();
  MedicationFrequencyType _frequency = MedicationFrequencyType.onceDaily;
  List<ReminderTimeInput> _times = const [
    ReminderTimeInput(timeOfDay: '08:00'),
  ];
  bool _refillEnabled = false;
  String? _localError;

  @override
  void dispose() {
    _nameController.dispose();
    _dosageController.dispose();
    _unitController.dispose();
    _instructionsController.dispose();
    _intervalController.dispose();
    _startDateController.dispose();
    _endDateController.dispose();
    _prescribedByController.dispose();
    _notesController.dispose();
    _refillQuantityController.dispose();
    _refillThresholdController.dispose();
    _refillDateController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(createMedicationReminderControllerProvider);
    final showTimes = _frequency != MedicationFrequencyType.asNeeded;
    return AppScaffold(
      title: l10n.get('addMedicationReminder'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const MedicationDisclaimerBox(),
          const SizedBox(height: 16),
          TextField(
            controller: _nameController,
            decoration: InputDecoration(labelText: l10n.get('medicationName')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _dosageController,
            decoration: InputDecoration(labelText: l10n.get('dosage')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _unitController,
            decoration: InputDecoration(labelText: l10n.get('dosageUnit')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _instructionsController,
            minLines: 2,
            maxLines: 4,
            decoration: InputDecoration(labelText: l10n.get('instructions')),
          ),
          const SizedBox(height: 16),
          Text(
            l10n.get('frequency'),
            style: Theme.of(context).textTheme.titleMedium,
          ),
          const SizedBox(height: 8),
          FrequencySelector(
            value: _frequency,
            onChanged: (frequency) {
              setState(() {
                _frequency = frequency;
                _times = _defaultTimes(frequency);
                _localError = null;
              });
            },
          ),
          if (_frequency == MedicationFrequencyType.everyXHours) ...[
            const SizedBox(height: 12),
            TextField(
              controller: _intervalController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(labelText: l10n.get('intervalHours')),
            ),
          ],
          const SizedBox(height: 16),
          if (showTimes)
            ReminderTimesEditor(
              times: _times,
              onChanged: (times) => setState(() => _times = times),
            ),
          const SizedBox(height: 12),
          TextField(
            controller: _startDateController,
            decoration: InputDecoration(
              labelText: l10n.get('startDate'),
              hintText: '2026-05-06',
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _endDateController,
            decoration: InputDecoration(
              labelText: l10n.get('endDate'),
              hintText: l10n.get('optional'),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _prescribedByController,
            decoration: InputDecoration(labelText: l10n.get('prescribedBy')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _notesController,
            minLines: 2,
            maxLines: 4,
            decoration: InputDecoration(labelText: l10n.get('notes')),
          ),
          const SizedBox(height: 12),
          SwitchListTile(
            value: _refillEnabled,
            onChanged: (value) => setState(() => _refillEnabled = value),
            title: Text(l10n.get('refill')),
          ),
          if (_refillEnabled) ...[
            TextField(
              controller: _refillQuantityController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: l10n.get('refillQuantity'),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _refillThresholdController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: l10n.get('refillThreshold'),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _refillDateController,
              decoration: InputDecoration(labelText: l10n.get('refillDate')),
            ),
          ],
          if (_localError != null || state.error != null) ...[
            const SizedBox(height: 12),
            Text(
              _localError ?? state.error!.message,
              style: TextStyle(color: Theme.of(context).colorScheme.error),
            ),
          ],
          const SizedBox(height: 20),
          AppButton(
            label: l10n.get('save'),
            isLoading: state.isSubmitting,
            onPressed: () async {
              final request = _request();
              final validation = MedicationReminderValidator.validate(request);
              if (validation != null) {
                setState(() => _localError = validation);
                return;
              }
              setState(() => _localError = null);
              final reminder = await ref
                  .read(createMedicationReminderControllerProvider.notifier)
                  .create(request);
              if (!context.mounted) return;
              if (reminder != null) {
                ref.invalidate(medicationRemindersControllerProvider);
                ref.invalidate(medicationsDashboardControllerProvider);
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(l10n.get('reminderCreated'))),
                );
                Navigator.of(context).pop();
              }
            },
          ),
        ],
      ),
    );
  }

  CreateMedicationReminderRequest _request() {
    return CreateMedicationReminderRequest(
      medicationName: _nameController.text,
      dosage: _dosageController.text,
      dosageUnit: _unitController.text,
      instructions: _instructionsController.text,
      frequencyType: _frequency,
      intervalHours: int.tryParse(_intervalController.text),
      startDate: _startDateController.text,
      endDate: _endDateController.text,
      prescribedBy: _prescribedByController.text,
      notes: _notesController.text,
      refillEnabled: _refillEnabled,
      refillQuantity: int.tryParse(_refillQuantityController.text),
      refillThreshold: int.tryParse(_refillThresholdController.text),
      refillReminderDate: _refillDateController.text,
      times: _frequency == MedicationFrequencyType.asNeeded ? const [] : _times,
    );
  }

  static List<ReminderTimeInput> _defaultTimes(MedicationFrequencyType type) {
    return switch (type) {
      MedicationFrequencyType.onceDaily => const [
        ReminderTimeInput(timeOfDay: '08:00'),
      ],
      MedicationFrequencyType.twiceDaily => const [
        ReminderTimeInput(timeOfDay: '08:00'),
        ReminderTimeInput(timeOfDay: '20:00'),
      ],
      MedicationFrequencyType.threeTimesDaily => const [
        ReminderTimeInput(timeOfDay: '08:00'),
        ReminderTimeInput(timeOfDay: '14:00'),
        ReminderTimeInput(timeOfDay: '20:00'),
      ],
      MedicationFrequencyType.customTimes => const [
        ReminderTimeInput(timeOfDay: '08:00'),
      ],
      MedicationFrequencyType.everyXHours => const [
        ReminderTimeInput(timeOfDay: '08:00'),
      ],
      MedicationFrequencyType.asNeeded => const [],
      MedicationFrequencyType.specificDays => const [
        ReminderTimeInput(timeOfDay: '08:00'),
      ],
      MedicationFrequencyType.unknown => const [],
    };
  }

  static String _todayDate() {
    final now = DateTime.now();
    String two(int number) => number.toString().padLeft(2, '0');
    return '${now.year}-${two(now.month)}-${two(now.day)}';
  }
}
