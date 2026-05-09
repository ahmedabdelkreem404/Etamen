import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/hospitals/data/datasources/hospitals_remote_data_source.dart';
import 'package:etamen_app/features/hospitals/data/repositories/hospitals_repository_impl.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital_department.dart';
import 'package:etamen_app/features/hospitals/domain/repositories/hospitals_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final hospitalsRemoteDataSourceProvider = Provider<HospitalsRemoteDataSource>((
  ref,
) {
  return HospitalsRemoteDataSource(ref.watch(apiClientProvider));
});

final hospitalsRepositoryProvider = Provider<HospitalsRepository>((ref) {
  return HospitalsRepositoryImpl(ref.watch(hospitalsRemoteDataSourceProvider));
});

class HospitalsState {
  const HospitalsState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.query = '',
  });

  final List<Hospital> items;
  final bool isLoading;
  final ApiError? error;
  final String query;

  List<Hospital> get filteredItems {
    final needle = query.trim().toLowerCase();
    if (needle.isEmpty) return items;
    return items
        .where(
          (item) =>
              item.name.toLowerCase().contains(needle) ||
              (item.primaryCityName?.toLowerCase().contains(needle) ?? false) ||
              (item.primaryAreaName?.toLowerCase().contains(needle) ?? false),
        )
        .toList(growable: false);
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  HospitalsState copyWith({
    List<Hospital>? items,
    bool? isLoading,
    ApiError? error,
    String? query,
    bool clearError = false,
  }) {
    return HospitalsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      query: query ?? this.query,
    );
  }
}

final hospitalsControllerProvider =
    StateNotifierProvider.autoDispose<HospitalsController, HospitalsState>((
      ref,
    ) {
      return HospitalsController(ref.watch(hospitalsRepositoryProvider))
        ..load();
    });

class HospitalsController extends StateNotifier<HospitalsState> {
  HospitalsController(this._repository) : super(const HospitalsState());

  final HospitalsRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.listHospitals();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void search(String value) => state = state.copyWith(query: value);
}

final hospitalDetailsProvider = FutureProvider.autoDispose
    .family<ApiResult<Hospital>, int>((ref, id) {
      return ref.watch(hospitalsRepositoryProvider).getHospital(id);
    });

final hospitalDepartmentsProvider = FutureProvider.autoDispose
    .family<ApiResult<List<HospitalDepartment>>, int>((ref, hospitalId) {
      return ref.watch(hospitalsRepositoryProvider).getDepartments(hospitalId);
    });

final hospitalDoctorsProvider = FutureProvider.autoDispose
    .family<ApiResult<List<Doctor>>, int>((ref, hospitalId) {
      return ref
          .watch(hospitalsRepositoryProvider)
          .getHospitalDoctors(hospitalId);
    });

class HospitalDepartmentDoctorsParams {
  const HospitalDepartmentDoctorsParams({
    required this.hospitalId,
    required this.departmentId,
  });

  final int hospitalId;
  final int departmentId;

  @override
  bool operator ==(Object other) {
    return other is HospitalDepartmentDoctorsParams &&
        other.hospitalId == hospitalId &&
        other.departmentId == departmentId;
  }

  @override
  int get hashCode => Object.hash(hospitalId, departmentId);
}

final hospitalDepartmentDoctorsProvider = FutureProvider.autoDispose
    .family<ApiResult<List<Doctor>>, HospitalDepartmentDoctorsParams>((
      ref,
      params,
    ) {
      return ref
          .watch(hospitalsRepositoryProvider)
          .getDepartmentDoctors(
            hospitalId: params.hospitalId,
            departmentId: params.departmentId,
          );
    });
