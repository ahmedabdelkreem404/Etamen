import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/doctors/data/datasources/doctors_remote_data_source.dart';
import 'package:etamen_app/features/doctors/data/repositories/doctors_repository_impl.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor_slot.dart';
import 'package:etamen_app/features/doctors/domain/repositories/doctors_repository.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final doctorsRemoteDataSourceProvider = Provider<DoctorsRemoteDataSource>((
  ref,
) {
  return DoctorsRemoteDataSource(ref.watch(apiClientProvider));
});

final doctorsRepositoryProvider = Provider<DoctorsRepository>((ref) {
  return DoctorsRepositoryImpl(ref.watch(doctorsRemoteDataSourceProvider));
});

class DoctorsListState {
  const DoctorsListState({
    this.doctors = const [],
    this.isLoading = false,
    this.error,
  });

  final List<Doctor> doctors;
  final bool isLoading;
  final ApiError? error;

  bool get isEmpty => !isLoading && error == null && doctors.isEmpty;

  DoctorsListState copyWith({
    List<Doctor>? doctors,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
  }) {
    return DoctorsListState(
      doctors: doctors ?? this.doctors,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final doctorsListControllerProvider =
    StateNotifierProvider<DoctorsListController, DoctorsListState>((ref) {
      return DoctorsListController(ref.watch(doctorsRepositoryProvider))
        ..load();
    });

class DoctorsListController extends StateNotifier<DoctorsListState> {
  DoctorsListController(this._repository) : super(const DoctorsListState());

  final DoctorsRepository _repository;

  Future<void> load({String? search}) async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.listDoctors(search: search);
    state = result.when(
      success: (doctors) => DoctorsListState(doctors: doctors),
      failure: (failure) => DoctorsListState(error: failure.error),
    );
  }
}

final doctorDetailsProvider = FutureProvider.family<ApiResult<Doctor>, int>((
  ref,
  id,
) {
  return ref.watch(doctorsRepositoryProvider).getDoctor(id);
});

final doctorSlotsProvider =
    FutureProvider.family<ApiResult<List<DoctorSlot>>, int>((ref, id) {
      return ref.watch(doctorsRepositoryProvider).getSlots(id);
    });
