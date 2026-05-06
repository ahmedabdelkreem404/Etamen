import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/health/data/models/create_vital_record_request.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/presentation/providers/health_providers.dart';
import 'package:etamen_app/features/health/presentation/widgets/health_disclaimer_box.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_type_selector.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class AddVitalPage extends ConsumerStatefulWidget {
  const AddVitalPage({this.initialType = VitalType.bloodPressure, super.key});

  final VitalType initialType;

  @override
  ConsumerState<AddVitalPage> createState() => _AddVitalPageState();
}

class _AddVitalPageState extends ConsumerState<AddVitalPage> {
  late VitalType _type;
  DateTime _measuredAt = DateTime.now();
  BloodSugarContext _sugarContext = BloodSugarContext.random;
  Mood _mood = Mood.neutral;
  final _primaryController = TextEditingController();
  final _secondaryController = TextEditingController();
  final _notesController = TextEditingController();
  String? _localError;

  @override
  void initState() {
    super.initState();
    _type = widget.initialType;
  }

  @override
  void dispose() {
    _primaryController.dispose();
    _secondaryController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(addVitalControllerProvider);

    return AppScaffold(
      title: l10n.get('addVital'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const HealthDisclaimerBox(),
          const SizedBox(height: 16),
          VitalTypeSelector(
            value: _type,
            onChanged: (type) {
              setState(() {
                _type = type;
                _primaryController.clear();
                _secondaryController.clear();
                _localError = null;
              });
            },
          ),
          const SizedBox(height: 16),
          ListTile(
            contentPadding: EdgeInsets.zero,
            leading: const Icon(Icons.schedule_outlined),
            title: Text(l10n.get('measuredAt')),
            subtitle: Text(_formatDate(_measuredAt)),
            trailing: TextButton(
              onPressed: () => setState(() => _measuredAt = DateTime.now()),
              child: Text(l10n.get('now')),
            ),
          ),
          ..._fieldsForType(context),
          const SizedBox(height: 12),
          TextField(
            controller: _notesController,
            minLines: 2,
            maxLines: 4,
            decoration: InputDecoration(labelText: l10n.get('notes')),
          ),
          if (_localError != null || state.error != null) ...[
            const SizedBox(height: 12),
            Text(
              _localError ?? state.error!.message,
              style: TextStyle(color: Theme.of(context).colorScheme.error),
            ),
          ],
          const SizedBox(height: 20),
          AppButton(
            label: l10n.get('saveVital'),
            isLoading: state.isSubmitting,
            onPressed: () async {
              final request = _buildRequest();
              final validation = request == null
                  ? l10n.get('requiredField')
                  : VitalInputValidator.validate(request);
              if (validation != null) {
                setState(() => _localError = validation);
                return;
              }

              setState(() => _localError = null);
              final record = await ref
                  .read(addVitalControllerProvider.notifier)
                  .submit(request!);
              if (!context.mounted) return;
              if (record != null) {
                ref.invalidate(healthDashboardControllerProvider);
                ref.invalidate(vitalsListControllerProvider);
                ScaffoldMessenger.of(
                  context,
                ).showSnackBar(SnackBar(content: Text(l10n.get('vitalSaved'))));
                Navigator.of(context).pop();
              }
            },
          ),
        ],
      ),
    );
  }

  List<Widget> _fieldsForType(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return switch (_type) {
      VitalType.bloodPressure => [
        _numberField(_primaryController, l10n.get('systolic')),
        const SizedBox(height: 12),
        _numberField(_secondaryController, l10n.get('diastolic')),
      ],
      VitalType.bloodSugar => [
        _numberField(_primaryController, l10n.get('bloodSugar')),
        const SizedBox(height: 12),
        DropdownButtonFormField<BloodSugarContext>(
          value: _sugarContext,
          decoration: InputDecoration(labelText: l10n.get('context')),
          items: BloodSugarContext.values
              .where((item) => item != BloodSugarContext.unknown)
              .map(
                (item) => DropdownMenuItem(
                  value: item,
                  child: Text(_bloodSugarContextLabel(context, item)),
                ),
              )
              .toList(growable: false),
          onChanged: (value) {
            if (value != null) setState(() => _sugarContext = value);
          },
        ),
      ],
      VitalType.heartRate => [
        _numberField(_primaryController, l10n.get('heartRate')),
      ],
      VitalType.oxygen => [
        _numberField(_primaryController, l10n.get('oxygen')),
      ],
      VitalType.temperature => [
        _numberField(_primaryController, l10n.get('temperature')),
      ],
      VitalType.weight => [
        _numberField(_primaryController, l10n.get('weight')),
      ],
      VitalType.sleep => [
        _numberField(_primaryController, l10n.get('sleepHours')),
        const SizedBox(height: 12),
        DropdownButtonFormField<String>(
          value: 'good',
          decoration: InputDecoration(labelText: l10n.get('sleepQuality')),
          items: const ['poor', 'fair', 'good', 'excellent']
              .map((item) => DropdownMenuItem(value: item, child: Text(item)))
              .toList(growable: false),
          onChanged: (value) {
            _secondaryController.text = value ?? '';
          },
        ),
      ],
      VitalType.mood => [
        DropdownButtonFormField<Mood>(
          value: _mood,
          decoration: InputDecoration(labelText: l10n.get('mood')),
          items: Mood.values
              .where((item) => item != Mood.unknown)
              .map(
                (item) => DropdownMenuItem(
                  value: item,
                  child: Text(_moodLabel(context, item)),
                ),
              )
              .toList(growable: false),
          onChanged: (value) {
            if (value != null) setState(() => _mood = value);
          },
        ),
      ],
      VitalType.symptoms => [
        TextField(
          controller: _primaryController,
          minLines: 2,
          maxLines: 4,
          decoration: InputDecoration(labelText: l10n.get('symptoms')),
        ),
      ],
      VitalType.unknown => const [],
    };
  }

  Widget _numberField(TextEditingController controller, String label) {
    return TextField(
      controller: controller,
      keyboardType: const TextInputType.numberWithOptions(decimal: true),
      decoration: InputDecoration(labelText: label),
    );
  }

  CreateVitalRecordRequest? _buildRequest() {
    final primary = num.tryParse(_primaryController.text.trim());
    final secondary = num.tryParse(_secondaryController.text.trim());
    final notes = _notesController.text;
    return switch (_type) {
      VitalType.bloodPressure when primary != null && secondary != null =>
        CreateVitalRecordRequest.bloodPressure(
          measuredAt: _measuredAt,
          systolic: primary,
          diastolic: secondary,
          notes: notes,
        ),
      VitalType.bloodSugar when primary != null =>
        CreateVitalRecordRequest.bloodSugar(
          measuredAt: _measuredAt,
          value: primary,
          context: _sugarContext,
          notes: notes,
        ),
      VitalType.heartRate when primary != null =>
        CreateVitalRecordRequest.simple(
          vitalType: VitalType.heartRate,
          measuredAt: _measuredAt,
          value: primary,
          notes: notes,
        ),
      VitalType.oxygen when primary != null => CreateVitalRecordRequest.simple(
        vitalType: VitalType.oxygen,
        measuredAt: _measuredAt,
        value: primary,
        notes: notes,
      ),
      VitalType.temperature when primary != null =>
        CreateVitalRecordRequest.simple(
          vitalType: VitalType.temperature,
          measuredAt: _measuredAt,
          value: primary,
          notes: notes,
        ),
      VitalType.weight when primary != null => CreateVitalRecordRequest.simple(
        vitalType: VitalType.weight,
        measuredAt: _measuredAt,
        value: primary,
        notes: notes,
      ),
      VitalType.sleep when primary != null => CreateVitalRecordRequest.simple(
        vitalType: VitalType.sleep,
        measuredAt: _measuredAt,
        value: primary,
        notes: notes,
        metadata: {
          if (_secondaryController.text.isNotEmpty)
            'quality': _secondaryController.text,
        },
      ),
      VitalType.mood => CreateVitalRecordRequest.mood(
        measuredAt: _measuredAt,
        mood: _mood,
        notes: notes,
      ),
      VitalType.symptoms when _primaryController.text.trim().isNotEmpty =>
        CreateVitalRecordRequest.symptoms(
          measuredAt: _measuredAt,
          symptoms: _primaryController.text,
          notes: notes,
        ),
      _ => null,
    };
  }

  static String _formatDate(DateTime value) {
    final local = value.toLocal();
    String two(int number) => number.toString().padLeft(2, '0');
    return '${local.year}-${two(local.month)}-${two(local.day)} ${two(local.hour)}:${two(local.minute)}';
  }

  static String _bloodSugarContextLabel(
    BuildContext context,
    BloodSugarContext contextValue,
  ) {
    final l10n = AppLocalizations.of(context);
    return switch (contextValue) {
      BloodSugarContext.fasting => l10n.get('fasting'),
      BloodSugarContext.afterMeal => l10n.get('afterMeal'),
      BloodSugarContext.random => l10n.get('random'),
      BloodSugarContext.beforeMeal => l10n.get('beforeMeal'),
      BloodSugarContext.bedtime => l10n.get('bedtime'),
      BloodSugarContext.unknown => l10n.get('unknown'),
    };
  }

  static String _moodLabel(BuildContext context, Mood mood) {
    final l10n = AppLocalizations.of(context);
    return switch (mood) {
      Mood.veryBad => l10n.get('veryBad'),
      Mood.bad => l10n.get('bad'),
      Mood.neutral => l10n.get('neutral'),
      Mood.good => l10n.get('good'),
      Mood.veryGood => l10n.get('veryGood'),
      Mood.unknown => l10n.get('unknown'),
    };
  }
}
