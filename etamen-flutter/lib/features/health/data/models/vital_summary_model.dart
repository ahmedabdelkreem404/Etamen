import 'package:etamen_app/features/health/data/models/vital_record_model.dart';
import 'package:etamen_app/features/health/domain/entities/vital_summary.dart';

class VitalSummaryModel extends VitalSummary {
  const VitalSummaryModel({
    super.profileCompletionPercentage,
    super.latestVitals,
    super.activeChronicDiseasesCount,
    super.activeAllergiesCount,
    super.activeCurrentMedicationsCount,
    super.activeGoalsCount,
    super.flagsCount,
    super.bmi,
    super.disclaimer,
    super.safeMessages,
  });

  factory VitalSummaryModel.fromJson(Map<String, dynamic> json) {
    final latestRaw = json['latest_vitals'];
    return VitalSummaryModel(
      profileCompletionPercentage: _toInt(
        json['profile_completion_percentage'],
      ),
      latestVitals: _parseVitals(latestRaw),
      activeChronicDiseasesCount: _toInt(json['active_chronic_diseases_count']),
      activeAllergiesCount: _toInt(json['active_allergies_count']),
      activeCurrentMedicationsCount: _toInt(
        json['active_current_medications_count'],
      ),
      activeGoalsCount: _toInt(json['active_goals_count']),
      flagsCount: _toInt(
        json['non_diagnostic_warning_flags_count'] ?? json['flags_count'],
      ),
      bmi: json['bmi']?.toString(),
      disclaimer: (json['safe_disclaimer'] ?? json['disclaimer'])?.toString(),
      safeMessages: _parseStrings(json['safe_messages']),
    );
  }

  static List<VitalRecordModel> _parseVitals(Object? value) {
    if (value is Map) {
      return value.values
          .whereType<Map>()
          .map((item) => VitalRecordModel.fromJson(_toMap(item)))
          .toList(growable: false);
    }
    if (value is List) {
      return value
          .whereType<Map>()
          .map((item) => VitalRecordModel.fromJson(_toMap(item)))
          .toList(growable: false);
    }
    return const [];
  }

  static List<String> _parseStrings(Object? value) {
    if (value is! List) return const [];
    return value.map((item) => item.toString()).toList(growable: false);
  }

  static Map<String, dynamic> _toMap(Map value) {
    return value.map((key, item) => MapEntry(key.toString(), item));
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }
}
