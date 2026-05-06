import 'package:etamen_app/features/health/domain/entities/vital_record.dart';

class VitalTrend {
  const VitalTrend({
    required this.vitalType,
    this.unit,
    this.from,
    this.to,
    this.points = const [],
    this.latestRecord,
    this.flagsSummary = const {},
    this.disclaimer,
  });

  final VitalType vitalType;
  final String? unit;
  final DateTime? from;
  final DateTime? to;
  final List<VitalTrendPoint> points;
  final VitalRecord? latestRecord;
  final Map<String, int> flagsSummary;
  final String? disclaimer;
}

class VitalTrendPoint {
  const VitalTrendPoint({
    this.measuredAt,
    this.value,
    this.secondaryValue,
    this.flag = VitalFlag.unknown,
    this.count,
  });

  final DateTime? measuredAt;
  final String? value;
  final String? secondaryValue;
  final VitalFlag flag;
  final int? count;
}
