import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/medications/data/datasources/medications_remote_data_source.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_log_request.dart';
import 'package:etamen_app/features/medications/data/models/create_medication_reminder_request.dart';
import 'package:etamen_app/features/medications/data/repositories/medications_repository_impl.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_adherence.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_log.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_refill_event.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';
import 'package:etamen_app/features/medications/domain/repositories/medications_repository.dart';
import 'package:etamen_app/features/medications/domain/usecases/cancel_medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/usecases/create_medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/usecases/get_medication_adherence.dart';
import 'package:etamen_app/features/medications/domain/usecases/get_medication_reminders.dart';
import 'package:etamen_app/features/medications/domain/usecases/get_refill_events.dart';
import 'package:etamen_app/features/medications/domain/usecases/get_today_medications.dart';
import 'package:etamen_app/features/medications/domain/usecases/get_upcoming_medications.dart';
import 'package:etamen_app/features/medications/domain/usecases/mark_medication_skipped.dart';
import 'package:etamen_app/features/medications/domain/usecases/mark_medication_taken.dart';
import 'package:etamen_app/features/medications/domain/usecases/pause_medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/usecases/record_refill_done.dart';
import 'package:etamen_app/features/medications/domain/usecases/record_refill_skipped.dart';
import 'package:etamen_app/features/medications/domain/usecases/resume_medication_reminder.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final medicationsRemoteDataSourceProvider =
    Provider<MedicationsRemoteDataSource>((ref) {
      return MedicationsRemoteDataSource(ref.watch(apiClientProvider));
    });

final medicationsRepositoryProvider = Provider<MedicationsRepository>((ref) {
  return MedicationsRepositoryImpl(
    ref.watch(medicationsRemoteDataSourceProvider),
  );
});

class MedicationsDashboardState {
  const MedicationsDashboardState({
    this.isLoading = false,
    this.reminders = const [],
    this.today = const [],
    this.upcoming = const [],
    this.refills = const [],
    this.adherence,
    this.error,
  });

  final bool isLoading;
  final List<MedicationReminder> reminders;
  final List<MedicationScheduleItem> today;
  final List<MedicationScheduleItem> upcoming;
  final List<MedicationRefillEvent> refills;
  final MedicationAdherence? adherence;
  final ApiError? error;

  bool get isEmpty =>
      !isLoading && error == null && reminders.isEmpty && today.isEmpty;

  int get activeReminderCount => reminders
      .where((item) => item.status == MedicationReminderStatus.active)
      .length;

  MedicationsDashboardState copyWith({
    bool? isLoading,
    List<MedicationReminder>? reminders,
    List<MedicationScheduleItem>? today,
    List<MedicationScheduleItem>? upcoming,
    List<MedicationRefillEvent>? refills,
    MedicationAdherence? adherence,
    ApiError? error,
    bool clearError = false,
  }) {
    return MedicationsDashboardState(
      isLoading: isLoading ?? this.isLoading,
      reminders: reminders ?? this.reminders,
      today: today ?? this.today,
      upcoming: upcoming ?? this.upcoming,
      refills: refills ?? this.refills,
      adherence: adherence ?? this.adherence,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final medicationsDashboardControllerProvider =
    StateNotifierProvider.autoDispose<
      MedicationsDashboardController,
      MedicationsDashboardState
    >((ref) {
      return MedicationsDashboardController(
        ref.watch(medicationsRepositoryProvider),
      )..load();
    });

class MedicationsDashboardController
    extends StateNotifier<MedicationsDashboardState> {
  MedicationsDashboardController(MedicationsRepository repository)
    : _getReminders = GetMedicationReminders(repository),
      _getToday = GetTodayMedications(repository),
      _getUpcoming = GetUpcomingMedications(repository),
      _getAdherence = GetMedicationAdherence(repository),
      _getRefills = GetRefillEvents(repository),
      super(const MedicationsDashboardState());

  final GetMedicationReminders _getReminders;
  final GetTodayMedications _getToday;
  final GetUpcomingMedications _getUpcoming;
  final GetMedicationAdherence _getAdherence;
  final GetRefillEvents _getRefills;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final remindersResult = await _getReminders();
    final todayResult = await _getToday();
    final upcomingResult = await _getUpcoming(days: 7);
    final adherenceResult = await _getAdherence();
    final refillsResult = await _getRefills();

    final error = remindersResult is ApiFailure<List<MedicationReminder>>
        ? remindersResult.error
        : todayResult is ApiFailure<List<MedicationScheduleItem>>
        ? todayResult.error
        : null;

    state = MedicationsDashboardState(
      isLoading: false,
      reminders: remindersResult is ApiSuccess<List<MedicationReminder>>
          ? remindersResult.data
          : const [],
      today: todayResult is ApiSuccess<List<MedicationScheduleItem>>
          ? todayResult.data
          : const [],
      upcoming: upcomingResult is ApiSuccess<List<MedicationScheduleItem>>
          ? upcomingResult.data
          : const [],
      adherence: adherenceResult is ApiSuccess<MedicationAdherence>
          ? adherenceResult.data
          : null,
      refills: refillsResult is ApiSuccess<List<MedicationRefillEvent>>
          ? refillsResult.data
          : const [],
      error: error,
    );
  }
}

enum ReminderFilter { all, active, paused, ended }

class MedicationRemindersState {
  const MedicationRemindersState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.filter = ReminderFilter.all,
  });

  final List<MedicationReminder> items;
  final bool isLoading;
  final ApiError? error;
  final ReminderFilter filter;

  List<MedicationReminder> get filteredItems {
    return switch (filter) {
      ReminderFilter.all => items,
      ReminderFilter.active =>
        items
            .where((item) => item.status == MedicationReminderStatus.active)
            .toList(growable: false),
      ReminderFilter.paused =>
        items
            .where((item) => item.status == MedicationReminderStatus.paused)
            .toList(growable: false),
      ReminderFilter.ended =>
        items
            .where(
              (item) =>
                  item.status == MedicationReminderStatus.cancelled ||
                  item.status == MedicationReminderStatus.completed,
            )
            .toList(growable: false),
    };
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  MedicationRemindersState copyWith({
    List<MedicationReminder>? items,
    bool? isLoading,
    ApiError? error,
    ReminderFilter? filter,
    bool clearError = false,
  }) {
    return MedicationRemindersState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      filter: filter ?? this.filter,
    );
  }
}

final medicationRemindersControllerProvider =
    StateNotifierProvider.autoDispose<
      MedicationRemindersController,
      MedicationRemindersState
    >((ref) {
      return MedicationRemindersController(
        ref.watch(medicationsRepositoryProvider),
      )..load();
    });

class MedicationRemindersController
    extends StateNotifier<MedicationRemindersState> {
  MedicationRemindersController(MedicationsRepository repository)
    : _getReminders = GetMedicationReminders(repository),
      _pause = PauseMedicationReminder(repository),
      _resume = ResumeMedicationReminder(repository),
      _cancel = CancelMedicationReminder(repository),
      super(const MedicationRemindersState());

  final GetMedicationReminders _getReminders;
  final PauseMedicationReminder _pause;
  final ResumeMedicationReminder _resume;
  final CancelMedicationReminder _cancel;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getReminders();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void selectFilter(ReminderFilter filter) {
    state = state.copyWith(filter: filter);
  }

  Future<void> pause(int reminderId) => _action(() => _pause(reminderId));

  Future<void> resume(int reminderId) => _action(() => _resume(reminderId));

  Future<void> cancel(int reminderId) => _action(() => _cancel(reminderId));

  Future<void> _action(
    Future<ApiResult<MedicationReminder>> Function() operation,
  ) async {
    final result = await operation();
    await result.when(success: (_) async => load(), failure: (_) async {});
  }
}

class CreateMedicationReminderState {
  const CreateMedicationReminderState({this.isSubmitting = false, this.error});

  final bool isSubmitting;
  final ApiError? error;

  CreateMedicationReminderState copyWith({
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return CreateMedicationReminderState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final createMedicationReminderControllerProvider =
    StateNotifierProvider.autoDispose<
      CreateMedicationReminderController,
      CreateMedicationReminderState
    >((ref) {
      return CreateMedicationReminderController(
        ref.watch(medicationsRepositoryProvider),
      );
    });

class CreateMedicationReminderController
    extends StateNotifier<CreateMedicationReminderState> {
  CreateMedicationReminderController(MedicationsRepository repository)
    : _createReminder = CreateMedicationReminder(repository),
      super(const CreateMedicationReminderState());

  final CreateMedicationReminder _createReminder;

  Future<MedicationReminder?> create(
    CreateMedicationReminderRequest request,
  ) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _createReminder(request);
    return result.when(
      success: (reminder) {
        state = state.copyWith(isSubmitting: false, clearError: true);
        return reminder;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return null;
      },
    );
  }
}

class MedicationReminderDetailsState {
  const MedicationReminderDetailsState({
    this.isLoading = false,
    this.isSubmitting = false,
    this.reminder,
    this.logs = const [],
    this.refills = const [],
    this.error,
  });

  final bool isLoading;
  final bool isSubmitting;
  final MedicationReminder? reminder;
  final List<MedicationLog> logs;
  final List<MedicationRefillEvent> refills;
  final ApiError? error;

  MedicationReminderDetailsState copyWith({
    bool? isLoading,
    bool? isSubmitting,
    MedicationReminder? reminder,
    List<MedicationLog>? logs,
    List<MedicationRefillEvent>? refills,
    ApiError? error,
    bool clearError = false,
  }) {
    return MedicationReminderDetailsState(
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      reminder: reminder ?? this.reminder,
      logs: logs ?? this.logs,
      refills: refills ?? this.refills,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final medicationReminderDetailsControllerProvider = StateNotifierProvider
    .autoDispose
    .family<
      MedicationReminderDetailsController,
      MedicationReminderDetailsState,
      int
    >((ref, reminderId) {
      return MedicationReminderDetailsController(
        reminderId,
        ref.watch(medicationsRepositoryProvider),
      )..load();
    });

class MedicationReminderDetailsController
    extends StateNotifier<MedicationReminderDetailsState> {
  MedicationReminderDetailsController(
    this.reminderId,
    MedicationsRepository repository,
  ) : _repository = repository,
      _pause = PauseMedicationReminder(repository),
      _resume = ResumeMedicationReminder(repository),
      _cancel = CancelMedicationReminder(repository),
      _markTaken = MarkMedicationTaken(repository),
      _refillDone = RecordRefillDone(repository),
      _refillSkipped = RecordRefillSkipped(repository),
      super(const MedicationReminderDetailsState());

  final int reminderId;
  final MedicationsRepository _repository;
  final PauseMedicationReminder _pause;
  final ResumeMedicationReminder _resume;
  final CancelMedicationReminder _cancel;
  final MarkMedicationTaken _markTaken;
  final RecordRefillDone _refillDone;
  final RecordRefillSkipped _refillSkipped;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final reminderResult = await _repository.getReminder(reminderId);
    final logsResult = await _repository.getLogs(reminderId: reminderId);
    final refillsResult = await _repository.getRefills();
    state = reminderResult.when(
      success: (reminder) => state.copyWith(
        isLoading: false,
        reminder: reminder,
        logs: logsResult is ApiSuccess<List<MedicationLog>>
            ? logsResult.data
            : const [],
        refills: refillsResult is ApiSuccess<List<MedicationRefillEvent>>
            ? refillsResult.data
                  .where((item) => item.medicationReminderId == reminderId)
                  .toList(growable: false)
            : const [],
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<void> pause() => _statusAction(() => _pause(reminderId));

  Future<void> resume() => _statusAction(() => _resume(reminderId));

  Future<void> cancel() => _statusAction(() => _cancel(reminderId));

  Future<bool> markAsNeededTaken() async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _markTaken(
      reminderId,
      QuickMedicationLogRequest(
        scheduledFor: DateTime.now(),
        takenAt: DateTime.now(),
      ),
    );
    return result.when(
      success: (_) async {
        state = state.copyWith(isSubmitting: false, clearError: true);
        await load();
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }

  Future<bool> refillDone() => _refillAction(() => _refillDone(reminderId));

  Future<bool> refillSkipped() =>
      _refillAction(() => _refillSkipped(reminderId));

  Future<void> _statusAction(
    Future<ApiResult<MedicationReminder>> Function() action,
  ) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await action();
    await result.when(
      success: (_) async {
        state = state.copyWith(isSubmitting: false, clearError: true);
        await load();
      },
      failure: (failure) async {
        state = state.copyWith(isSubmitting: false, error: failure.error);
      },
    );
  }

  Future<bool> _refillAction(
    Future<ApiResult<MedicationRefillEvent>> Function() action,
  ) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await action();
    return result.when(
      success: (_) async {
        state = state.copyWith(isSubmitting: false, clearError: true);
        await load();
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}

class TodayMedicationsState {
  const TodayMedicationsState({
    this.items = const [],
    this.isLoading = false,
    this.isSubmitting = false,
    this.error,
  });

  final List<MedicationScheduleItem> items;
  final bool isLoading;
  final bool isSubmitting;
  final ApiError? error;

  bool get isEmpty => !isLoading && error == null && items.isEmpty;

  TodayMedicationsState copyWith({
    List<MedicationScheduleItem>? items,
    bool? isLoading,
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return TodayMedicationsState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final todayMedicationsControllerProvider =
    StateNotifierProvider.autoDispose<
      TodayMedicationsController,
      TodayMedicationsState
    >((ref) {
      return TodayMedicationsController(
        ref.watch(medicationsRepositoryProvider),
      )..load();
    });

class TodayMedicationsController extends StateNotifier<TodayMedicationsState> {
  TodayMedicationsController(MedicationsRepository repository)
    : _getToday = GetTodayMedications(repository),
      _markTaken = MarkMedicationTaken(repository),
      _markSkipped = MarkMedicationSkipped(repository),
      super(const TodayMedicationsState());

  final GetTodayMedications _getToday;
  final MarkMedicationTaken _markTaken;
  final MarkMedicationSkipped _markSkipped;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getToday();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  Future<bool> markTaken(MedicationScheduleItem item) {
    return _logAction(
      () => _markTaken(
        item.reminderId,
        QuickMedicationLogRequest(
          scheduledFor: item.scheduledFor,
          takenAt: DateTime.now(),
        ),
      ),
    );
  }

  Future<bool> markSkipped(MedicationScheduleItem item) {
    return _logAction(
      () => _markSkipped(
        item.reminderId,
        QuickMedicationLogRequest(scheduledFor: item.scheduledFor),
      ),
    );
  }

  Future<bool> _logAction(
    Future<ApiResult<MedicationLog>> Function() operation,
  ) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await operation();
    return result.when(
      success: (_) async {
        state = state.copyWith(isSubmitting: false, clearError: true);
        await load();
        return true;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return false;
      },
    );
  }
}

class MedicationAdherenceState {
  const MedicationAdherenceState({
    this.isLoading = false,
    this.adherence,
    this.error,
  });

  final bool isLoading;
  final MedicationAdherence? adherence;
  final ApiError? error;

  MedicationAdherenceState copyWith({
    bool? isLoading,
    MedicationAdherence? adherence,
    ApiError? error,
    bool clearError = false,
  }) {
    return MedicationAdherenceState(
      isLoading: isLoading ?? this.isLoading,
      adherence: adherence ?? this.adherence,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final medicationAdherenceControllerProvider =
    StateNotifierProvider.autoDispose<
      MedicationAdherenceController,
      MedicationAdherenceState
    >((ref) {
      return MedicationAdherenceController(
        ref.watch(medicationsRepositoryProvider),
      )..load();
    });

class MedicationAdherenceController
    extends StateNotifier<MedicationAdherenceState> {
  MedicationAdherenceController(MedicationsRepository repository)
    : _getAdherence = GetMedicationAdherence(repository),
      super(const MedicationAdherenceState());

  final GetMedicationAdherence _getAdherence;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getAdherence();
    state = result.when(
      success: (adherence) => state.copyWith(
        isLoading: false,
        adherence: adherence,
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}
