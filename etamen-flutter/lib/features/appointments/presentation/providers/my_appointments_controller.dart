import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/features/appointments/domain/entities/appointment.dart';
import 'package:etamen_app/features/appointments/domain/repositories/appointments_repository.dart';
import 'package:etamen_app/features/appointments/domain/usecases/get_my_appointments.dart';
import 'package:etamen_app/features/appointments/presentation/providers/appointment_booking_controller.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

enum AppointmentListFilter {
  all,
  upcoming,
  pendingPayment,
  completed,
  cancelled,
}

class MyAppointmentsState {
  const MyAppointmentsState({
    this.items = const [],
    this.isLoading = false,
    this.isRefreshing = false,
    this.error,
    this.selectedFilter = AppointmentListFilter.all,
  });

  final List<Appointment> items;
  final bool isLoading;
  final bool isRefreshing;
  final ApiError? error;
  final AppointmentListFilter selectedFilter;

  List<Appointment> get filteredItems {
    return switch (selectedFilter) {
      AppointmentListFilter.all => items,
      AppointmentListFilter.upcoming =>
        items.where((item) => item.isUpcoming).toList(growable: false),
      AppointmentListFilter.pendingPayment =>
        items
            .where(
              (item) =>
                  item.status == AppointmentStatus.pendingPayment ||
                  item.status == AppointmentStatus.pendingPaymentReview,
            )
            .toList(growable: false),
      AppointmentListFilter.completed =>
        items.where((item) => item.isCompleted).toList(growable: false),
      AppointmentListFilter.cancelled =>
        items.where((item) => item.isCancelled).toList(growable: false),
    };
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  MyAppointmentsState copyWith({
    List<Appointment>? items,
    bool? isLoading,
    bool? isRefreshing,
    ApiError? error,
    AppointmentListFilter? selectedFilter,
    bool clearError = false,
  }) {
    return MyAppointmentsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      isRefreshing: isRefreshing ?? this.isRefreshing,
      error: clearError ? null : error ?? this.error,
      selectedFilter: selectedFilter ?? this.selectedFilter,
    );
  }
}

final myAppointmentsControllerProvider =
    StateNotifierProvider.autoDispose<
      MyAppointmentsController,
      MyAppointmentsState
    >((ref) {
      return MyAppointmentsController(ref.watch(appointmentsRepositoryProvider))
        ..load();
    });

class MyAppointmentsController extends StateNotifier<MyAppointmentsState> {
  MyAppointmentsController(AppointmentsRepository repository)
    : _getMyAppointments = GetMyAppointments(repository),
      super(const MyAppointmentsState());

  final GetMyAppointments _getMyAppointments;

  Future<void> load({bool refresh = false}) async {
    state = state.copyWith(
      isLoading: !refresh,
      isRefreshing: refresh,
      clearError: true,
    );

    final result = await _getMyAppointments(perPage: 20);
    state = result.when(
      success: (items) => state.copyWith(
        items: items,
        isLoading: false,
        isRefreshing: false,
        clearError: true,
      ),
      failure: (failure) => state.copyWith(
        isLoading: false,
        isRefreshing: false,
        error: failure.error,
      ),
    );
  }

  void selectFilter(AppointmentListFilter filter) {
    state = state.copyWith(selectedFilter: filter);
  }
}
