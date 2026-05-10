import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/fitness/data/datasources/fitness_remote_data_source.dart';
import 'package:etamen_app/features/fitness/data/models/fitness_models.dart';
import 'package:etamen_app/features/fitness/data/repositories/fitness_repository_impl.dart';
import 'package:etamen_app/features/fitness/domain/entities/fitness_entities.dart';
import 'package:etamen_app/features/fitness/domain/repositories/fitness_repository.dart';
import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';
import 'package:etamen_app/features/payments/domain/repositories/payments_repository.dart';
import 'package:etamen_app/features/payments/domain/usecases/get_payment_status.dart';
import 'package:etamen_app/features/payments/presentation/providers/payment_controller.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final fitnessRemoteDataSourceProvider = Provider<FitnessRemoteDataSource>((
  ref,
) {
  return FitnessRemoteDataSource(ref.watch(apiClientProvider));
});

final fitnessRepositoryProvider = Provider<FitnessRepository>((ref) {
  return FitnessRepositoryImpl(ref.watch(fitnessRemoteDataSourceProvider));
});

class GymsState {
  const GymsState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.query = '',
  });

  final List<Gym> items;
  final bool isLoading;
  final ApiError? error;
  final String query;

  List<Gym> get filteredItems {
    final needle = query.trim().toLowerCase();
    if (needle.isEmpty) return items;
    return items
        .where(
          (item) =>
              item.nameAr.toLowerCase().contains(needle) ||
              (item.nameEn?.toLowerCase().contains(needle) ?? false) ||
              item.locationLabel.toLowerCase().contains(needle),
        )
        .toList(growable: false);
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  GymsState copyWith({
    List<Gym>? items,
    bool? isLoading,
    ApiError? error,
    String? query,
    bool clearError = false,
  }) {
    return GymsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      query: query ?? this.query,
    );
  }
}

final gymsControllerProvider =
    StateNotifierProvider.autoDispose<GymsController, GymsState>((ref) {
      return GymsController(ref.watch(fitnessRepositoryProvider))..load();
    });

class GymsController extends StateNotifier<GymsState> {
  GymsController(this._repository) : super(const GymsState());

  final FitnessRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.getGyms();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void search(String value) => state = state.copyWith(query: value);
}

class GymDetailsState {
  const GymDetailsState({
    this.isLoading = false,
    this.gym,
    this.plans = const [],
    this.classes = const [],
    this.error,
  });

  final bool isLoading;
  final Gym? gym;
  final List<GymMembershipPlan> plans;
  final List<GymClass> classes;
  final ApiError? error;

  GymDetailsState copyWith({
    bool? isLoading,
    Gym? gym,
    List<GymMembershipPlan>? plans,
    List<GymClass>? classes,
    ApiError? error,
    bool clearError = false,
  }) {
    return GymDetailsState(
      isLoading: isLoading ?? this.isLoading,
      gym: gym ?? this.gym,
      plans: plans ?? this.plans,
      classes: classes ?? this.classes,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final gymDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<GymDetailsController, GymDetailsState, int>((ref, gymId) {
      return GymDetailsController(gymId, ref.watch(fitnessRepositoryProvider))
        ..load();
    });

class GymDetailsController extends StateNotifier<GymDetailsState> {
  GymDetailsController(this.gymId, this._repository)
    : super(const GymDetailsState());

  final int gymId;
  final FitnessRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final gymResult = await _repository.getGym(gymId);
    final plansResult = await _repository.getGymMembershipPlans(gymId);
    final classesResult = await _repository.getGymClasses(gymId);

    final gym = gymResult is ApiSuccess<Gym> ? gymResult.data : null;
    final plans = plansResult is ApiSuccess<List<GymMembershipPlan>>
        ? plansResult.data
        : <GymMembershipPlan>[];
    final classes = classesResult is ApiSuccess<List<GymClass>>
        ? classesResult.data
        : <GymClass>[];
    final error = gymResult is ApiFailure<Gym>
        ? gymResult.error
        : plansResult is ApiFailure<List<GymMembershipPlan>>
        ? plansResult.error
        : classesResult is ApiFailure<List<GymClass>>
        ? classesResult.error
        : null;

    state = state.copyWith(
      gym: gym,
      plans: plans,
      classes: classes,
      isLoading: false,
      error: error,
      clearError: error == null,
    );
  }
}

class CreateFitnessBookingState {
  const CreateFitnessBookingState({this.isSubmitting = false, this.error});

  final bool isSubmitting;
  final ApiError? error;

  CreateFitnessBookingState copyWith({
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return CreateFitnessBookingState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final createGymBookingControllerProvider =
    StateNotifierProvider.autoDispose<
      CreateGymBookingController,
      CreateFitnessBookingState
    >((ref) {
      return CreateGymBookingController(ref.watch(fitnessRepositoryProvider));
    });

class CreateGymBookingController
    extends StateNotifier<CreateFitnessBookingState> {
  CreateGymBookingController(this._repository)
    : super(const CreateFitnessBookingState());

  final FitnessRepository _repository;

  Future<GymBooking?> create(CreateGymBookingRequest request) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _repository.createGymBooking(request);
    return result.when(
      success: (booking) async {
        final resolvedBooking = await _resolveCreatedBooking(booking);
        state = state.copyWith(isSubmitting: false, clearError: true);
        return resolvedBooking;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return null;
      },
    );
  }

  Future<GymBooking> _resolveCreatedBooking(GymBooking booking) async {
    if (booking.id > 0 &&
        (booking.paymentId != null || !booking.status.canPay)) {
      return booking;
    }

    final result = await _repository.getGymBookings();
    return result.when(
      success: (items) {
        if (booking.bookingNumber?.trim().isNotEmpty == true) {
          return items.firstWhere(
            (item) => item.bookingNumber == booking.bookingNumber,
            orElse: () => items.isNotEmpty ? items.first : booking,
          );
        }
        return items.isNotEmpty ? items.first : booking;
      },
      failure: (_) => booking,
    );
  }
}

class GymBookingsState {
  const GymBookingsState({
    this.items = const [],
    this.isLoading = false,
    this.error,
  });

  final List<GymBooking> items;
  final bool isLoading;
  final ApiError? error;

  bool get isEmpty => !isLoading && error == null && items.isEmpty;

  GymBookingsState copyWith({
    List<GymBooking>? items,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
  }) {
    return GymBookingsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final gymBookingsControllerProvider =
    StateNotifierProvider.autoDispose<GymBookingsController, GymBookingsState>((
      ref,
    ) {
      return GymBookingsController(ref.watch(fitnessRepositoryProvider))
        ..load();
    });

class GymBookingsController extends StateNotifier<GymBookingsState> {
  GymBookingsController(this._repository) : super(const GymBookingsState());

  final FitnessRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.getGymBookings();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}

class GymBookingDetailsState {
  const GymBookingDetailsState({
    this.isLoading = false,
    this.booking,
    this.paymentStatus,
    this.error,
  });

  final bool isLoading;
  final GymBooking? booking;
  final PaymentStatusDetails? paymentStatus;
  final ApiError? error;

  GymBookingDetailsState copyWith({
    bool? isLoading,
    GymBooking? booking,
    PaymentStatusDetails? paymentStatus,
    ApiError? error,
    bool clearError = false,
  }) {
    return GymBookingDetailsState(
      isLoading: isLoading ?? this.isLoading,
      booking: booking ?? this.booking,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final gymBookingDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<GymBookingDetailsController, GymBookingDetailsState, int>((
      ref,
      bookingId,
    ) {
      return GymBookingDetailsController(
        bookingId,
        ref.watch(fitnessRepositoryProvider),
        ref.watch(paymentsRepositoryProvider),
      )..load();
    });

class GymBookingDetailsController
    extends StateNotifier<GymBookingDetailsState> {
  GymBookingDetailsController(
    this.bookingId,
    FitnessRepository fitnessRepository,
    PaymentsRepository paymentsRepository,
  ) : _fitnessRepository = fitnessRepository,
      _getPaymentStatus = GetPaymentStatus(paymentsRepository),
      super(const GymBookingDetailsState());

  final int bookingId;
  final FitnessRepository _fitnessRepository;
  final GetPaymentStatus _getPaymentStatus;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _fitnessRepository.getGymBooking(bookingId);
    await result.when(
      success: (booking) async {
        state = state.copyWith(
          booking: booking,
          isLoading: false,
          clearError: true,
        );
        await _loadPaymentStatus(booking.paymentId);
      },
      failure: (failure) async {
        state = state.copyWith(isLoading: false, error: failure.error);
      },
    );
  }

  Future<void> _loadPaymentStatus(int? paymentId) async {
    if (paymentId == null) return;
    final result = await _getPaymentStatus(paymentId);
    state = result.when(
      success: (status) => state.copyWith(paymentStatus: status),
      failure: (failure) => state.copyWith(error: failure.error),
    );
  }
}

class CoachesState {
  const CoachesState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.query = '',
  });

  final List<Coach> items;
  final bool isLoading;
  final ApiError? error;
  final String query;

  List<Coach> get filteredItems {
    final needle = query.trim().toLowerCase();
    if (needle.isEmpty) return items;
    return items
        .where(
          (item) =>
              item.nameAr.toLowerCase().contains(needle) ||
              (item.nameEn?.toLowerCase().contains(needle) ?? false) ||
              (item.coachType?.toLowerCase().contains(needle) ?? false),
        )
        .toList(growable: false);
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  CoachesState copyWith({
    List<Coach>? items,
    bool? isLoading,
    ApiError? error,
    String? query,
    bool clearError = false,
  }) {
    return CoachesState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      query: query ?? this.query,
    );
  }
}

final coachesControllerProvider =
    StateNotifierProvider.autoDispose<CoachesController, CoachesState>((ref) {
      return CoachesController(ref.watch(fitnessRepositoryProvider))..load();
    });

class CoachesController extends StateNotifier<CoachesState> {
  CoachesController(this._repository) : super(const CoachesState());

  final FitnessRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.getCoaches();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void search(String value) => state = state.copyWith(query: value);
}

class CoachDetailsState {
  const CoachDetailsState({
    this.isLoading = false,
    this.coach,
    this.sessionTypes = const [],
    this.availability = const [],
    this.packages = const [],
    this.selectedSessionType,
    this.selectedSlot,
    this.patientGoal = '',
    this.error,
  });

  final bool isLoading;
  final Coach? coach;
  final List<CoachSessionType> sessionTypes;
  final List<CoachAvailabilitySlot> availability;
  final List<CoachPackage> packages;
  final CoachSessionType? selectedSessionType;
  final CoachAvailabilitySlot? selectedSlot;
  final String patientGoal;
  final ApiError? error;

  CoachDetailsState copyWith({
    bool? isLoading,
    Coach? coach,
    List<CoachSessionType>? sessionTypes,
    List<CoachAvailabilitySlot>? availability,
    List<CoachPackage>? packages,
    CoachSessionType? selectedSessionType,
    CoachAvailabilitySlot? selectedSlot,
    String? patientGoal,
    ApiError? error,
    bool clearSelectedSlot = false,
    bool clearError = false,
  }) {
    return CoachDetailsState(
      isLoading: isLoading ?? this.isLoading,
      coach: coach ?? this.coach,
      sessionTypes: sessionTypes ?? this.sessionTypes,
      availability: availability ?? this.availability,
      packages: packages ?? this.packages,
      selectedSessionType: selectedSessionType ?? this.selectedSessionType,
      selectedSlot: clearSelectedSlot
          ? null
          : selectedSlot ?? this.selectedSlot,
      patientGoal: patientGoal ?? this.patientGoal,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final coachDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<CoachDetailsController, CoachDetailsState, int>((ref, coachId) {
      return CoachDetailsController(
        coachId,
        ref.watch(fitnessRepositoryProvider),
      )..load();
    });

class CoachDetailsController extends StateNotifier<CoachDetailsState> {
  CoachDetailsController(this.coachId, this._repository)
    : super(const CoachDetailsState());

  final int coachId;
  final FitnessRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final coachResult = await _repository.getCoach(coachId);
    final sessionsResult = await _repository.getCoachSessionTypes(coachId);
    final availabilityResult = await _repository.getCoachAvailability(coachId);
    final packagesResult = await _repository.getCoachPackages(coachId);

    final coach = coachResult is ApiSuccess<Coach> ? coachResult.data : null;
    final sessionTypes = sessionsResult is ApiSuccess<List<CoachSessionType>>
        ? sessionsResult.data
        : <CoachSessionType>[];
    final availability =
        availabilityResult is ApiSuccess<List<CoachAvailabilitySlot>>
        ? availabilityResult.data
        : <CoachAvailabilitySlot>[];
    final packages = packagesResult is ApiSuccess<List<CoachPackage>>
        ? packagesResult.data
        : <CoachPackage>[];
    final error = coachResult is ApiFailure<Coach>
        ? coachResult.error
        : sessionsResult is ApiFailure<List<CoachSessionType>>
        ? sessionsResult.error
        : availabilityResult is ApiFailure<List<CoachAvailabilitySlot>>
        ? availabilityResult.error
        : packagesResult is ApiFailure<List<CoachPackage>>
        ? packagesResult.error
        : null;

    state = state.copyWith(
      coach: coach,
      sessionTypes: sessionTypes,
      availability: availability,
      packages: packages,
      isLoading: false,
      error: error,
      clearError: error == null,
    );
  }

  void selectSessionType(CoachSessionType sessionType) {
    state = state.copyWith(selectedSessionType: sessionType, clearError: true);
  }

  void selectSlot(CoachAvailabilitySlot slot) {
    state = state.copyWith(selectedSlot: slot, clearError: true);
  }

  void updateGoal(String value) => state = state.copyWith(patientGoal: value);
}

final createCoachBookingControllerProvider =
    StateNotifierProvider.autoDispose<
      CreateCoachBookingController,
      CreateFitnessBookingState
    >((ref) {
      return CreateCoachBookingController(ref.watch(fitnessRepositoryProvider));
    });

class CreateCoachBookingController
    extends StateNotifier<CreateFitnessBookingState> {
  CreateCoachBookingController(this._repository)
    : super(const CreateFitnessBookingState());

  final FitnessRepository _repository;

  Future<CoachBooking?> create(CreateCoachBookingRequest request) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _repository.createCoachBooking(request);
    return result.when(
      success: (booking) async {
        final resolvedBooking = await _resolveCreatedBooking(booking);
        state = state.copyWith(isSubmitting: false, clearError: true);
        return resolvedBooking;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return null;
      },
    );
  }

  Future<CoachBooking> _resolveCreatedBooking(CoachBooking booking) async {
    if (booking.id > 0 &&
        (booking.paymentId != null || !booking.status.canPay)) {
      return booking;
    }

    final result = await _repository.getCoachBookings();
    return result.when(
      success: (items) {
        if (booking.bookingNumber?.trim().isNotEmpty == true) {
          return items.firstWhere(
            (item) => item.bookingNumber == booking.bookingNumber,
            orElse: () => items.isNotEmpty ? items.first : booking,
          );
        }
        return items.isNotEmpty ? items.first : booking;
      },
      failure: (_) => booking,
    );
  }
}

class CoachBookingsState {
  const CoachBookingsState({
    this.items = const [],
    this.isLoading = false,
    this.error,
  });

  final List<CoachBooking> items;
  final bool isLoading;
  final ApiError? error;

  bool get isEmpty => !isLoading && error == null && items.isEmpty;

  CoachBookingsState copyWith({
    List<CoachBooking>? items,
    bool? isLoading,
    ApiError? error,
    bool clearError = false,
  }) {
    return CoachBookingsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final coachBookingsControllerProvider =
    StateNotifierProvider.autoDispose<
      CoachBookingsController,
      CoachBookingsState
    >((ref) {
      return CoachBookingsController(ref.watch(fitnessRepositoryProvider))
        ..load();
    });

class CoachBookingsController extends StateNotifier<CoachBookingsState> {
  CoachBookingsController(this._repository) : super(const CoachBookingsState());

  final FitnessRepository _repository;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _repository.getCoachBookings();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}

class CoachBookingDetailsState {
  const CoachBookingDetailsState({
    this.isLoading = false,
    this.booking,
    this.paymentStatus,
    this.error,
  });

  final bool isLoading;
  final CoachBooking? booking;
  final PaymentStatusDetails? paymentStatus;
  final ApiError? error;

  CoachBookingDetailsState copyWith({
    bool? isLoading,
    CoachBooking? booking,
    PaymentStatusDetails? paymentStatus,
    ApiError? error,
    bool clearError = false,
  }) {
    return CoachBookingDetailsState(
      isLoading: isLoading ?? this.isLoading,
      booking: booking ?? this.booking,
      paymentStatus: paymentStatus ?? this.paymentStatus,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final coachBookingDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<CoachBookingDetailsController, CoachBookingDetailsState, int>((
      ref,
      bookingId,
    ) {
      return CoachBookingDetailsController(
        bookingId,
        ref.watch(fitnessRepositoryProvider),
        ref.watch(paymentsRepositoryProvider),
      )..load();
    });

class CoachBookingDetailsController
    extends StateNotifier<CoachBookingDetailsState> {
  CoachBookingDetailsController(
    this.bookingId,
    FitnessRepository fitnessRepository,
    PaymentsRepository paymentsRepository,
  ) : _fitnessRepository = fitnessRepository,
      _getPaymentStatus = GetPaymentStatus(paymentsRepository),
      super(const CoachBookingDetailsState());

  final int bookingId;
  final FitnessRepository _fitnessRepository;
  final GetPaymentStatus _getPaymentStatus;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _fitnessRepository.getCoachBooking(bookingId);
    await result.when(
      success: (booking) async {
        state = state.copyWith(
          booking: booking,
          isLoading: false,
          clearError: true,
        );
        await _loadPaymentStatus(booking.paymentId);
      },
      failure: (failure) async {
        state = state.copyWith(isLoading: false, error: failure.error);
      },
    );
  }

  Future<void> _loadPaymentStatus(int? paymentId) async {
    if (paymentId == null) return;
    final result = await _getPaymentStatus(paymentId);
    state = result.when(
      success: (status) => state.copyWith(paymentStatus: status),
      failure: (failure) => state.copyWith(error: failure.error),
    );
  }
}
