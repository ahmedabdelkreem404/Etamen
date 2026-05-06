import 'package:etamen_app/features/medications/domain/entities/medication_adherence.dart';

class MedicationAdherenceModel extends MedicationAdherence {
  const MedicationAdherenceModel({
    super.totalScheduled,
    super.takenCount,
    super.skippedCount,
    super.missedCount,
    super.adherencePercentage,
    super.from,
    super.to,
    super.byReminder,
    super.disclaimer,
  });

  factory MedicationAdherenceModel.fromJson(Map<String, dynamic> json) {
    return MedicationAdherenceModel(
      totalScheduled: _toInt(json['total_scheduled']) ?? 0,
      takenCount: _toInt(json['taken_count']) ?? 0,
      skippedCount: _toInt(json['skipped_count']) ?? 0,
      missedCount: _toInt(json['missed_count']) ?? 0,
      adherencePercentage: _toDouble(json['adherence_percentage']) ?? 0,
      from: json['from']?.toString(),
      to: json['to']?.toString(),
      byReminder: _parseMaps(json['by_reminder']),
      disclaimer: json['disclaimer']?.toString(),
    );
  }

  static List<Map<String, dynamic>> _parseMaps(Object? value) {
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map(
          (item) => item.map((key, value) => MapEntry(key.toString(), value)),
        )
        .toList(growable: false);
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }

  static double? _toDouble(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    return double.tryParse(value.toString());
  }
}
