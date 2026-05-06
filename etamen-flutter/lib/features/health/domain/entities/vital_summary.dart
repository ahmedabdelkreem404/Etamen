import 'package:etamen_app/features/health/domain/entities/vital_record.dart';

class VitalSummary {
  const VitalSummary({
    this.profileCompletionPercentage,
    this.latestVitals = const [],
    this.activeChronicDiseasesCount,
    this.activeAllergiesCount,
    this.activeCurrentMedicationsCount,
    this.activeGoalsCount,
    this.flagsCount,
    this.bmi,
    this.disclaimer,
    this.safeMessages = const [],
  });

  final int? profileCompletionPercentage;
  final List<VitalRecord> latestVitals;
  final int? activeChronicDiseasesCount;
  final int? activeAllergiesCount;
  final int? activeCurrentMedicationsCount;
  final int? activeGoalsCount;
  final int? flagsCount;
  final String? bmi;
  final String? disclaimer;
  final List<String> safeMessages;
}
