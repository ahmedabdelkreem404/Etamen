import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/health/data/datasources/health_remote_data_source.dart';
import 'package:etamen_app/features/health/data/models/create_vital_record_request.dart';
import 'package:etamen_app/features/health/data/models/update_health_profile_request.dart';
import 'package:etamen_app/features/health/data/repositories/health_repository_impl.dart';
import 'package:etamen_app/features/health/domain/entities/health_profile.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/domain/entities/vital_summary.dart';
import 'package:etamen_app/features/health/domain/entities/vital_trend.dart';
import 'package:etamen_app/features/health/domain/repositories/health_repository.dart';
import 'package:etamen_app/features/health/domain/usecases/create_vital_record.dart';
import 'package:etamen_app/features/health/domain/usecases/get_health_profile.dart';
import 'package:etamen_app/features/health/domain/usecases/get_latest_vitals.dart';
import 'package:etamen_app/features/health/domain/usecases/get_vital_trends.dart';
import 'package:etamen_app/features/health/domain/usecases/get_vitals.dart';
import 'package:etamen_app/features/health/domain/usecases/get_vitals_summary.dart';
import 'package:etamen_app/features/health/domain/usecases/update_health_profile.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final healthRemoteDataSourceProvider = Provider<HealthRemoteDataSource>((ref) {
  return HealthRemoteDataSource(ref.watch(apiClientProvider));
});

final healthRepositoryProvider = Provider<HealthRepository>((ref) {
  return HealthRepositoryImpl(ref.watch(healthRemoteDataSourceProvider));
});

class HealthDashboardState {
  const HealthDashboardState({
    this.isLoading = false,
    this.profile,
    this.latestVitals = const [],
    this.summary,
    this.trend,
    this.error,
  });

  final bool isLoading;
  final HealthProfile? profile;
  final List<VitalRecord> latestVitals;
  final VitalSummary? summary;
  final VitalTrend? trend;
  final ApiError? error;

  bool get isEmpty =>
      !isLoading &&
      error == null &&
      latestVitals.isEmpty &&
      summary?.latestVitals.isEmpty != false;

  HealthDashboardState copyWith({
    bool? isLoading,
    HealthProfile? profile,
    List<VitalRecord>? latestVitals,
    VitalSummary? summary,
    VitalTrend? trend,
    ApiError? error,
    bool clearError = false,
  }) {
    return HealthDashboardState(
      isLoading: isLoading ?? this.isLoading,
      profile: profile ?? this.profile,
      latestVitals: latestVitals ?? this.latestVitals,
      summary: summary ?? this.summary,
      trend: trend ?? this.trend,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final healthDashboardControllerProvider =
    StateNotifierProvider.autoDispose<
      HealthDashboardController,
      HealthDashboardState
    >((ref) {
      return HealthDashboardController(ref.watch(healthRepositoryProvider))
        ..load();
    });

class HealthDashboardController extends StateNotifier<HealthDashboardState> {
  HealthDashboardController(HealthRepository repository)
    : _getProfile = GetHealthProfile(repository),
      _getLatest = GetLatestVitals(repository),
      _getSummary = GetVitalsSummary(repository),
      _getTrends = GetVitalTrends(repository),
      super(const HealthDashboardState());

  final GetHealthProfile _getProfile;
  final GetLatestVitals _getLatest;
  final GetVitalsSummary _getSummary;
  final GetVitalTrends _getTrends;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final profileResult = await _getProfile();
    final summaryResult = await _getSummary();
    final latestResult = await _getLatest();
    final trendResult = await _getTrends(type: VitalType.bloodPressure);

    final profile = profileResult is ApiSuccess<HealthProfile>
        ? profileResult.data
        : null;
    final summary = summaryResult is ApiSuccess<VitalSummary>
        ? summaryResult.data
        : null;
    final latest = latestResult is ApiSuccess<List<VitalRecord>>
        ? latestResult.data
        : summary?.latestVitals ?? const <VitalRecord>[];
    final trend = trendResult is ApiSuccess<VitalTrend>
        ? trendResult.data
        : null;
    final error = profileResult is ApiFailure<HealthProfile>
        ? profileResult.error
        : summaryResult is ApiFailure<VitalSummary>
        ? summaryResult.error
        : latestResult is ApiFailure<List<VitalRecord>>
        ? latestResult.error
        : null;

    state = HealthDashboardState(
      isLoading: false,
      profile: profile,
      summary: summary,
      latestVitals: latest,
      trend: trend,
      error: error,
    );
  }
}

class HealthProfileState {
  const HealthProfileState({
    this.isLoading = false,
    this.isSubmitting = false,
    this.profile,
    this.error,
  });

  final bool isLoading;
  final bool isSubmitting;
  final HealthProfile? profile;
  final ApiError? error;

  HealthProfileState copyWith({
    bool? isLoading,
    bool? isSubmitting,
    HealthProfile? profile,
    ApiError? error,
    bool clearError = false,
  }) {
    return HealthProfileState(
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      profile: profile ?? this.profile,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final healthProfileControllerProvider =
    StateNotifierProvider.autoDispose<
      HealthProfileController,
      HealthProfileState
    >((ref) {
      return HealthProfileController(ref.watch(healthRepositoryProvider))
        ..load();
    });

class HealthProfileController extends StateNotifier<HealthProfileState> {
  HealthProfileController(HealthRepository repository)
    : _getProfile = GetHealthProfile(repository),
      _updateProfile = UpdateHealthProfile(repository),
      super(const HealthProfileState());

  final GetHealthProfile _getProfile;
  final UpdateHealthProfile _updateProfile;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getProfile();
    state = result.when(
      success: (profile) => state.copyWith(
        isLoading: false,
        profile: profile,
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<bool> update(UpdateHealthProfileRequest request) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _updateProfile(request);
    return result.when(
      success: (profile) {
        state = state.copyWith(
          isSubmitting: false,
          profile: profile,
          clearError: true,
        );
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}

class AddVitalState {
  const AddVitalState({this.isSubmitting = false, this.error});

  final bool isSubmitting;
  final ApiError? error;

  AddVitalState copyWith({
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return AddVitalState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final addVitalControllerProvider =
    StateNotifierProvider.autoDispose<AddVitalController, AddVitalState>((ref) {
      return AddVitalController(ref.watch(healthRepositoryProvider));
    });

class AddVitalController extends StateNotifier<AddVitalState> {
  AddVitalController(HealthRepository repository)
    : _createVital = CreateVitalRecord(repository),
      super(const AddVitalState());

  final CreateVitalRecord _createVital;

  Future<VitalRecord?> submit(CreateVitalRecordRequest request) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _createVital(request);
    return result.when(
      success: (record) {
        state = state.copyWith(isSubmitting: false, clearError: true);
        return record;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return null;
      },
    );
  }
}

class VitalsListState {
  const VitalsListState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.selectedType,
  });

  final List<VitalRecord> items;
  final bool isLoading;
  final ApiError? error;
  final VitalType? selectedType;

  bool get isEmpty => !isLoading && error == null && items.isEmpty;

  VitalsListState copyWith({
    List<VitalRecord>? items,
    bool? isLoading,
    ApiError? error,
    VitalType? selectedType,
    bool clearType = false,
    bool clearError = false,
  }) {
    return VitalsListState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      selectedType: clearType ? null : selectedType ?? this.selectedType,
    );
  }
}

final vitalsListControllerProvider =
    StateNotifierProvider.autoDispose<VitalsListController, VitalsListState>((
      ref,
    ) {
      return VitalsListController(ref.watch(healthRepositoryProvider))..load();
    });

class VitalsListController extends StateNotifier<VitalsListState> {
  VitalsListController(HealthRepository repository)
    : _getVitals = GetVitals(repository),
      super(const VitalsListState());

  final GetVitals _getVitals;

  Future<void> load({VitalType? type}) async {
    state = state.copyWith(
      isLoading: true,
      selectedType: type,
      clearType: type == null,
      clearError: true,
    );
    final result = await _getVitals(type: type);
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<void> selectType(VitalType? type) => load(type: type);
}
