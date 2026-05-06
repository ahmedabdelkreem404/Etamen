import 'package:etamen_app/core/config/api_endpoints.dart';
import 'package:etamen_app/core/network/api_client.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/data/models/create_vital_record_request.dart';
import 'package:etamen_app/features/health/data/models/health_profile_model.dart';
import 'package:etamen_app/features/health/data/models/update_health_profile_request.dart';
import 'package:etamen_app/features/health/data/models/vital_record_model.dart';
import 'package:etamen_app/features/health/data/models/vital_summary_model.dart';
import 'package:etamen_app/features/health/data/models/vital_trend_model.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';

class HealthRemoteDataSource {
  const HealthRemoteDataSource(this._client);

  final ApiClient _client;

  Future<ApiResult<HealthProfileModel>> getProfile() {
    return _client.get<HealthProfileModel>(
      ApiEndpoints.healthProfile,
      parser: (raw) => HealthProfileModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<HealthProfileModel>> updateProfile(
    UpdateHealthProfileRequest request,
  ) {
    return _client.put<HealthProfileModel>(
      ApiEndpoints.healthProfile,
      data: request.toJson(),
      parser: (raw) => HealthProfileModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<VitalRecordModel>>> getVitals({
    VitalType? type,
    int perPage = 20,
  }) {
    final query = <String, dynamic>{'per_page': perPage};
    if (type != null && type != VitalType.unknown) {
      query['vital_type'] = type.wireValue;
    }
    return _client.get<List<VitalRecordModel>>(
      ApiEndpoints.healthVitals,
      queryParameters: query,
      parser: (raw) => _parseList(raw)
          .map(VitalRecordModel.fromJson)
          .toList(growable: false),
    );
  }

  Future<ApiResult<VitalRecordModel>> createVital(
    CreateVitalRecordRequest request,
  ) {
    return _client.post<VitalRecordModel>(
      ApiEndpoints.healthVitals,
      data: request.toJson(),
      parser: (raw) => VitalRecordModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<List<VitalRecordModel>>> getLatestVitals() {
    return _client.get<List<VitalRecordModel>>(
      ApiEndpoints.healthLatestVitals,
      parser: (raw) => _parseLatest(raw)
          .map(VitalRecordModel.fromJson)
          .toList(growable: false),
    );
  }

  Future<ApiResult<VitalSummaryModel>> getSummary() {
    return _client.get<VitalSummaryModel>(
      ApiEndpoints.healthSummary,
      parser: (raw) => VitalSummaryModel.fromJson(_unwrapMap(raw)),
    );
  }

  Future<ApiResult<VitalTrendModel>> getTrends({
    required VitalType type,
    DateTime? from,
    DateTime? to,
  }) {
    final query = <String, dynamic>{'vital_type': type.wireValue};
    if (from != null) query['from'] = _dateOnly(from);
    if (to != null) query['to'] = _dateOnly(to);
    return _client.get<VitalTrendModel>(
      ApiEndpoints.healthVitalTrends,
      queryParameters: query,
      parser: (raw) => VitalTrendModel.fromJson(_unwrapMap(raw)),
    );
  }

  static List<Map<String, dynamic>> _parseList(Object? raw) {
    final value = _unwrapCollection(raw);
    if (value is! List) return const [];
    return value
        .whereType<Map>()
        .map((item) => item.map((key, value) => MapEntry(key.toString(), value)))
        .toList(growable: false);
  }

  static List<Map<String, dynamic>> _parseLatest(Object? raw) {
    final value = _unwrapCollection(raw);
    if (value is Map) {
      return value.values
          .whereType<Map>()
          .map((item) => item.map((key, value) => MapEntry(key.toString(), value)))
          .toList(growable: false);
    }
    if (value is List) return _parseList(value);
    return const [];
  }

  static Object? _unwrapCollection(Object? raw) {
    if (raw is Map) {
      return raw['data'] ?? raw['items'] ?? raw['vitals'] ?? raw['latest_vitals'];
    }
    return raw;
  }

  static Map<String, dynamic> _unwrapMap(Object? raw) {
    if (raw is Map<String, dynamic>) {
      final nested =
          raw['data'] ??
          raw['profile'] ??
          raw['vital'] ??
          raw['summary'] ??
          raw['trend'];
      if (nested is Map<String, dynamic>) return nested;
      if (nested is Map) {
        return nested.map((key, value) => MapEntry(key.toString(), value));
      }
      return raw;
    }
    if (raw is Map) {
      return raw.map((key, value) => MapEntry(key.toString(), value));
    }
    return const {};
  }

  static String _dateOnly(DateTime value) {
    return value.toIso8601String().split('T').first;
  }
}
