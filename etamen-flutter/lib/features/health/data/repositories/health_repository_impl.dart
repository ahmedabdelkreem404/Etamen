import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/data/datasources/health_remote_data_source.dart';
import 'package:etamen_app/features/health/data/models/create_vital_record_request.dart';
import 'package:etamen_app/features/health/data/models/update_health_profile_request.dart';
import 'package:etamen_app/features/health/domain/entities/health_profile.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/domain/entities/vital_summary.dart';
import 'package:etamen_app/features/health/domain/entities/vital_trend.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';

class HealthRepositoryImpl implements HealthRepository {
  const HealthRepositoryImpl(this._remoteDataSource);

  final HealthRemoteDataSource _remoteDataSource;

  @override
  Future<ApiResult<HealthProfile>> getProfile() {
    return _remoteDataSource.getProfile();
  }

  @override
  Future<ApiResult<HealthProfile>> updateProfile(
    UpdateHealthProfileRequest request,
  ) {
    return _remoteDataSource.updateProfile(request);
  }

  @override
  Future<ApiResult<List<VitalRecord>>> getVitals({VitalType? type}) {
    return _remoteDataSource.getVitals(type: type);
  }

  @override
  Future<ApiResult<VitalRecord>> createVital(CreateVitalRecordRequest request) {
    return _remoteDataSource.createVital(request);
  }

  @override
  Future<ApiResult<List<VitalRecord>>> getLatestVitals() {
    return _remoteDataSource.getLatestVitals();
  }

  @override
  Future<ApiResult<VitalSummary>> getSummary() {
    return _remoteDataSource.getSummary();
  }

  @override
  Future<ApiResult<VitalTrend>> getTrends({required VitalType type}) {
    return _remoteDataSource.getTrends(type: type);
  }
}
