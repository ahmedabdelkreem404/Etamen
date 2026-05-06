import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/data/models/create_vital_record_request.dart';
import 'package:etamen_app/features/health/data/models/update_health_profile_request.dart';
import 'package:etamen_app/features/health/domain/entities/health_profile.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/domain/entities/vital_summary.dart';
import 'package:etamen_app/features/health/domain/entities/vital_trend.dart';

abstract class HealthRepository {
  Future<ApiResult<HealthProfile>> getProfile();

  Future<ApiResult<HealthProfile>> updateProfile(
    UpdateHealthProfileRequest request,
  );

  Future<ApiResult<List<VitalRecord>>> getVitals({VitalType? type});

  Future<ApiResult<VitalRecord>> createVital(CreateVitalRecordRequest request);

  Future<ApiResult<List<VitalRecord>>> getLatestVitals();

  Future<ApiResult<VitalSummary>> getSummary();

  Future<ApiResult<VitalTrend>> getTrends({required VitalType type});
}
