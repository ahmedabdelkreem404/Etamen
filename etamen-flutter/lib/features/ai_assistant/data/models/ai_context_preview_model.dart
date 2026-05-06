import 'package:etamen_app/features/ai_assistant/data/models/ai_message_metadata_sanitizer.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_context_preview.dart';

class AiContextPreviewModel extends AiContextPreview {
  const AiContextPreviewModel({
    super.enabled,
    super.age,
    super.gender,
    super.latestVitals,
    super.chronicDiseases,
    super.allergies,
    super.currentMedications,
    super.medicationAdherence,
    super.carePlanSummary,
    super.disclaimer,
    super.privacyNote,
  });

  factory AiContextPreviewModel.fromJson(Map<String, dynamic> json) {
    final context = _asMap(json['context']) ?? json;
    final profile = _asMap(context['profile']) ?? const {};
    return AiContextPreviewModel(
      enabled: _toBool(context['enabled'] ?? json['enabled']),
      age: _toInt(profile['age'] ?? context['age']),
      gender: _string(profile['gender'] ?? context['gender']),
      latestVitals: _mapList(context['latest_vitals']),
      chronicDiseases: _stringList(
        context['active_chronic_diseases'] ?? context['chronic_diseases'],
      ),
      allergies: _stringList(
        context['active_allergies'] ?? context['allergies'],
      ),
      currentMedications: _stringList(context['current_medications']),
      medicationAdherence: _safeMap(context['medication_adherence_summary']),
      carePlanSummary: _mapList(
        context['active_care_plans'] ?? context['care_plan_summary'],
      ),
      disclaimer: _string(context['notice'] ?? json['disclaimer']),
      privacyNote: _string(json['privacy_note']),
    );
  }
}

Map<String, dynamic>? _safeMap(Object? value) {
  final map = _asMap(value);
  return map == null ? null : const AiMessageMetadataSanitizer().sanitize(map);
}

List<Map<String, dynamic>> _mapList(Object? value) {
  if (value is! List) return const [];
  return value
      .whereType<Map>()
      .map(
        (item) => const AiMessageMetadataSanitizer().sanitize(
          item.map((key, value) => MapEntry(key.toString(), value)),
        ),
      )
      .where((item) => item.isNotEmpty)
      .toList(growable: false);
}

List<String> _stringList(Object? value) {
  if (value is! List) return const [];
  return value
      .where((item) => item != null && item.toString().trim().isNotEmpty)
      .map((item) => item.toString())
      .toList(growable: false);
}

Map<String, dynamic>? _asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) {
    return value.map((key, value) => MapEntry(key.toString(), value));
  }
  return null;
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

bool? _toBool(Object? value) {
  if (value == null) return null;
  if (value is bool) return value;
  if (value is num) return value != 0;
  final text = value.toString().toLowerCase();
  if (text == 'true' || text == '1') return true;
  if (text == 'false' || text == '0') return false;
  return null;
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}
