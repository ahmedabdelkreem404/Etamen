import 'package:etamen_app/features/health/data/models/vital_record_model.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/domain/entities/vital_trend.dart';

class VitalTrendModel extends VitalTrend {
  const VitalTrendModel({
    required super.vitalType,
    super.unit,
    super.from,
    super.to,
    super.points,
    super.latestRecord,
    super.flagsSummary,
    super.disclaimer,
  });

  factory VitalTrendModel.fromJson(Map<String, dynamic> json) {
    final range = _asMap(json['range']);
    return VitalTrendModel(
      vitalType: VitalType.fromWire(json['vital_type']),
      unit: json['unit']?.toString(),
      from: _toDateTime(range['from'] ?? json['from']),
      to: _toDateTime(range['to'] ?? json['to']),
      points: _parsePoints(json['points']),
      latestRecord: _asNullableMap(json['latest_record']) == null
          ? null
          : VitalRecordModel.fromJson(_asNullableMap(json['latest_record'])!),
      flagsSummary: _parseFlags(json['flags_summary']),
      disclaimer: (json['safe_disclaimer'] ?? json['disclaimer'])?.toString(),
    );
  }

  static List<VitalTrendPoint> _parsePoints(Object? value) {
    if (value is! List) return const [];
    return value.whereType<Map>().map((item) {
      final map = _asMap(item);
      return VitalTrendPoint(
        measuredAt: _toDateTime(
          map['measured_at'] ?? map['date'] ?? map['bucket'],
        ),
        value: _toStringNumber(
          map['value'] ?? map['average'] ?? map['avg_value'],
        ),
        secondaryValue: _toStringNumber(
          map['secondary_value'] ?? map['average_secondary'],
        ),
        flag: VitalFlag.fromWire(map['flag']),
        count: _toInt(map['count']),
      );
    }).toList(growable: false);
  }

  static Map<String, int> _parseFlags(Object? value) {
    final map = _asMap(value);
    return map.map((key, value) => MapEntry(key, _toInt(value) ?? 0));
  }

  static Map<String, dynamic> _asMap(Object? value) {
    if (value is Map<String, dynamic>) return value;
    if (value is Map) {
      return value.map((key, item) => MapEntry(key.toString(), item));
    }
    return const {};
  }

  static Map<String, dynamic>? _asNullableMap(Object? value) {
    if (value == null) return null;
    return _asMap(value);
  }

  static DateTime? _toDateTime(Object? value) {
    if (value == null) return null;
    return DateTime.tryParse(value.toString());
  }

  static String? _toStringNumber(Object? value) {
    if (value == null) return null;
    return value.toString();
  }

  static int? _toInt(Object? value) {
    if (value == null) return null;
    if (value is num) return value.toInt();
    return int.tryParse(value.toString());
  }
}
