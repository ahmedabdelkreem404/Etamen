import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/features/health/data/models/update_health_profile_request.dart';
import 'package:etamen_app/features/health/presentation/providers/health_providers.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class EditHealthProfilePage extends ConsumerStatefulWidget {
  const EditHealthProfilePage({super.key});

  @override
  ConsumerState<EditHealthProfilePage> createState() => _EditHealthProfilePageState();
}

class _EditHealthProfilePageState extends ConsumerState<EditHealthProfilePage> {
  final _birthDateController = TextEditingController();
  final _genderController = TextEditingController();
  final _heightController = TextEditingController();
  final _weightController = TextEditingController();
  final _bloodTypeController = TextEditingController();
  final _notesController = TextEditingController();
  bool _seeded = false;

  @override
  void dispose() {
    _birthDateController.dispose();
    _genderController.dispose();
    _heightController.dispose();
    _weightController.dispose();
    _bloodTypeController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(healthProfileControllerProvider);
    final profile = state.profile;
    if (!_seeded && profile != null) {
      _seeded = true;
      _birthDateController.text = profile.birthDate ?? '';
      _genderController.text = profile.gender ?? '';
      _heightController.text = profile.heightCm ?? '';
      _weightController.text = profile.weightKg ?? '';
      _bloodTypeController.text = profile.bloodType ?? '';
      _notesController.text = profile.notes ?? '';
    }

    return AppScaffold(
      title: l10n.get('editProfile'),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          TextField(
            controller: _birthDateController,
            decoration: InputDecoration(
              labelText: l10n.get('birthDate'),
              hintText: '1990-01-01',
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _genderController,
            decoration: InputDecoration(labelText: l10n.get('gender')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _heightController,
            keyboardType: TextInputType.number,
            decoration: InputDecoration(labelText: l10n.get('heightCm')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _weightController,
            keyboardType: TextInputType.number,
            decoration: InputDecoration(labelText: l10n.get('weightKg')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _bloodTypeController,
            decoration: InputDecoration(labelText: l10n.get('bloodType')),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _notesController,
            minLines: 2,
            maxLines: 4,
            decoration: InputDecoration(labelText: l10n.get('notes')),
          ),
          if (state.error != null) ...[
            const SizedBox(height: 12),
            Text(
              state.error!.message,
              style: TextStyle(color: Theme.of(context).colorScheme.error),
            ),
          ],
          const SizedBox(height: 20),
          AppButton(
            label: l10n.get('save'),
            isLoading: state.isSubmitting,
            onPressed: () async {
              final ok = await ref
                  .read(healthProfileControllerProvider.notifier)
                  .update(
                    UpdateHealthProfileRequest(
                      birthDate: _birthDateController.text,
                      gender: _genderController.text,
                      heightCm: num.tryParse(_heightController.text),
                      weightKg: num.tryParse(_weightController.text),
                      bloodType: _bloodTypeController.text,
                      notes: _notesController.text,
                    ),
                  );
              if (!context.mounted) return;
              if (ok) {
                ScaffoldMessenger.of(context).showSnackBar(
                  SnackBar(content: Text(l10n.get('profileUpdated'))),
                );
                Navigator.of(context).pop();
              }
            },
          ),
        ],
      ),
    );
  }
}
