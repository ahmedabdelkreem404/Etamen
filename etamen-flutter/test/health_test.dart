import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/health/data/models/create_vital_record_request.dart';
import 'package:etamen_app/features/health/data/models/health_profile_model.dart';
import 'package:etamen_app/features/health/data/models/update_health_profile_request.dart';
import 'package:etamen_app/features/health/data/models/vital_record_model.dart';
import 'package:etamen_app/features/health/data/models/vital_summary_model.dart';
import 'package:etamen_app/features/health/data/models/vital_trend_model.dart';
import 'package:etamen_app/features/health/domain/entities/health_profile.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/domain/entities/vital_summary.dart';
import 'package:etamen_app/features/health/domain/entities/vital_trend.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';
import 'package:etamen_app/features/health/presentation/providers/health_providers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('HealthProfileModel parses nullable safe profile fields', () {
    final profile = HealthProfileModel.fromJson({
      'id': 3,
      'date_of_birth': '1990-01-01',
      'gender': 'male',
      'height_cm': '178.5',
      'weight_kg': 82,
      'blood_type': 'O+',
      'allergies': [
        {'name': 'Penicillin'},
      ],
    });

    expect(profile.id, 3);
    expect(profile.birthDate, '1990-01-01');
    expect(profile.heightCm, '178.5');
    expect(profile.weightKg, '82');
    expect(profile.allergies, ['Penicillin']);
  });

  test('VitalRecordModel parses all supported vital types safely', () {
    final cases = {
      'blood_pressure': VitalType.bloodPressure,
      'blood_sugar': VitalType.bloodSugar,
      'heart_rate': VitalType.heartRate,
      'oxygen_saturation': VitalType.oxygen,
      'temperature': VitalType.temperature,
      'weight': VitalType.weight,
      'sleep': VitalType.sleep,
      'mood': VitalType.mood,
      'symptom': VitalType.symptoms,
    };

    for (final entry in cases.entries) {
      final record = VitalRecordModel.fromJson({
        'id': 1,
        'vital_type': entry.key,
        'measured_at': '2026-05-06T10:00:00Z',
        'value_decimal': '120',
        'value_secondary_decimal': '80',
        'unit': 'mmHg',
        'flag': 'normal',
        'metadata': {'context': 'fasting', 'mood': 'good'},
      });
      expect(record.vitalType, entry.value);
      expect(record.flag, VitalFlag.normal);
    }
  });

  test('VitalType and VitalFlag enum mapping tolerate unknown values', () {
    expect(VitalType.fromWire('blood_pressure'), VitalType.bloodPressure);
    expect(VitalType.fromWire('not-real'), VitalType.unknown);
    expect(VitalFlag.fromWire('very_high'), VitalFlag.veryHigh);
    expect(VitalFlag.fromWire('not-real'), VitalFlag.unknown);
  });

  test('CreateVitalRecordRequest excludes forbidden ownership and backend fields', () {
    final request = CreateVitalRecordRequest.bloodSugar(
      measuredAt: DateTime.utc(2026, 5, 6, 10),
      value: 110,
      context: BloodSugarContext.fasting,
      notes: 'Before breakfast',
    );
    final json = request.toJson();

    expect(json['vital_type'], 'blood_sugar');
    expect(json['value_decimal'], 110);
    expect(json['metadata'], {'context': 'fasting'});
    expect(json.containsKey('patient_user_id'), false);
    expect(json.containsKey('user_id'), false);
    expect(json.containsKey('source'), false);
    expect(json.containsKey('flag'), false);
    expect(json.containsKey('unit'), false);
    expect(json.containsKey('diagnosis'), false);
    expect(json.containsKey('treatment'), false);
  });

  test('Blood pressure request serializes systolic and diastolic only', () {
    final json = CreateVitalRecordRequest.bloodPressure(
      measuredAt: DateTime.utc(2026, 5, 6, 10),
      systolic: 120,
      diastolic: 80,
    ).toJson();

    expect(json['vital_type'], 'blood_pressure');
    expect(json['value_decimal'], 120);
    expect(json['value_secondary_decimal'], 80);
    expect(json.containsKey('metadata'), false);
    expect(json.containsKey('unit'), false);
  });

  test('VitalSummaryModel and VitalTrendModel parse latest and trend data', () {
    final summary = VitalSummaryModel.fromJson({
      'profile_completion_percentage': 70,
      'latest_vitals': [
        {'id': 1, 'vital_type': 'weight', 'value_decimal': '82'},
      ],
      'non_diagnostic_warning_flags_count': 1,
      'bmi': '25.8',
      'safe_disclaimer': 'tracking only',
    });
    final trend = VitalTrendModel.fromJson({
      'vital_type': 'blood_pressure',
      'unit': 'mmHg',
      'range': {'from': '2026-05-01', 'to': '2026-05-06'},
      'points': [
        {'date': '2026-05-06', 'average': '120', 'average_secondary': '80'},
      ],
      'flags_summary': {'normal': 1},
    });

    expect(summary.latestVitals, hasLength(1));
    expect(summary.flagsCount, 1);
    expect(trend.vitalType, VitalType.bloodPressure);
    expect(trend.points.first.secondaryValue, '80');
  });

  test('Add vital validation rejects missing fields without medical wording', () {
    final request = CreateVitalRecordRequest(
      vitalType: VitalType.bloodPressure,
      measuredAt: DateTime.utc(2026, 5, 6),
    );
    final message = VitalInputValidator.validate(request);

    expect(message, isNotNull);
    expect(message!.contains('تشخيص'), false);
    expect(message.contains('علاج'), false);
  });

  test('VitalsListController filters by selected type', () async {
    final controller = VitalsListController(FakeHealthRepository());

    await controller.load(type: VitalType.weight);

    expect(controller.state.selectedType, VitalType.weight);
    expect(controller.state.items.single.vitalType, VitalType.weight);
  });
}

class FakeHealthRepository implements HealthRepository {
  @override
  Future<ApiResult<VitalRecord>> createVital(CreateVitalRecordRequest request) {
    return Future.value(
      ApiSuccess(
        VitalRecord(id: 1, vitalType: request.vitalType, measuredAt: request.measuredAt),
      ),
    );
  }

  @override
  Future<ApiResult<HealthProfile>> getProfile() {
    return Future.value(const ApiSuccess(HealthProfile(id: 1)));
  }

  @override
  Future<ApiResult<VitalSummary>> getSummary() {
    return Future.value(const ApiSuccess(VitalSummary()));
  }

  @override
  Future<ApiResult<VitalTrend>> getTrends({required VitalType type}) {
    return Future.value(ApiSuccess(VitalTrend(vitalType: type)));
  }

  @override
  Future<ApiResult<List<VitalRecord>>> getLatestVitals() {
    return Future.value(
      const ApiSuccess([VitalRecord(id: 1, vitalType: VitalType.weight)]),
    );
  }

  @override
  Future<ApiResult<List<VitalRecord>>> getVitals({VitalType? type}) {
    return Future.value(
      ApiSuccess([
        VitalRecord(id: 1, vitalType: type ?? VitalType.weight),
      ]),
    );
  }

  @override
  Future<ApiResult<HealthProfile>> updateProfile(
    UpdateHealthProfileRequest request,
  ) {
    return Future.value(const ApiSuccess(HealthProfile(id: 1)));
  }
}
