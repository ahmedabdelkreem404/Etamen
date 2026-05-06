import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/core/providers/core_providers.dart';
import 'package:etamen_app/features/care_plans/data/datasources/care_plans_remote_data_source.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_checkin_request.dart';
import 'package:etamen_app/features/care_plans/data/models/create_meal_log_request.dart';
import 'package:etamen_app/features/care_plans/data/repositories/care_plans_repository_impl.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_day.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_food_item.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_instruction.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_progress.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/create_care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/create_meal_log.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_care_plan_checkins.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_care_plan_days.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_care_plan_details.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_care_plan_foods.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_care_plan_instructions.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_care_plan_meals.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_care_plan_progress.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_care_plans.dart';
import 'package:etamen_app/features/care_plans/domain/usecases/get_meal_logs.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final carePlansRemoteDataSourceProvider = Provider<CarePlansRemoteDataSource>((
  ref,
) {
  return CarePlansRemoteDataSource(ref.watch(apiClientProvider));
});

final carePlansRepositoryProvider = Provider<CarePlansRepository>((ref) {
  return CarePlansRepositoryImpl(ref.watch(carePlansRemoteDataSourceProvider));
});

enum CarePlanFilter { all, active, paused, completed, cancelled }

class CarePlansState {
  const CarePlansState({
    this.items = const [],
    this.isLoading = false,
    this.error,
    this.filter = CarePlanFilter.all,
  });

  final List<CarePlan> items;
  final bool isLoading;
  final ApiError? error;
  final CarePlanFilter filter;

  List<CarePlan> get filteredItems {
    return switch (filter) {
      CarePlanFilter.all => items,
      CarePlanFilter.active =>
        items
            .where((item) => item.status == CarePlanStatus.active)
            .toList(growable: false),
      CarePlanFilter.paused =>
        items
            .where((item) => item.status == CarePlanStatus.paused)
            .toList(growable: false),
      CarePlanFilter.completed =>
        items
            .where((item) => item.status == CarePlanStatus.completed)
            .toList(growable: false),
      CarePlanFilter.cancelled =>
        items
            .where((item) => item.status == CarePlanStatus.cancelled)
            .toList(growable: false),
    };
  }

  bool get isEmpty => !isLoading && error == null && filteredItems.isEmpty;

  CarePlansState copyWith({
    List<CarePlan>? items,
    bool? isLoading,
    ApiError? error,
    CarePlanFilter? filter,
    bool clearError = false,
  }) {
    return CarePlansState(
      items: items ?? this.items,
      isLoading: isLoading ?? this.isLoading,
      error: clearError ? null : error ?? this.error,
      filter: filter ?? this.filter,
    );
  }
}

final carePlansControllerProvider =
    StateNotifierProvider.autoDispose<CarePlansController, CarePlansState>((
      ref,
    ) {
      return CarePlansController(ref.watch(carePlansRepositoryProvider))
        ..load();
    });

class CarePlansController extends StateNotifier<CarePlansState> {
  CarePlansController(CarePlansRepository repository)
    : _getCarePlans = GetCarePlans(repository),
      super(const CarePlansState());

  final GetCarePlans _getCarePlans;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getCarePlans();
    state = result.when(
      success: (items) =>
          state.copyWith(items: items, isLoading: false, clearError: true),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }

  void selectFilter(CarePlanFilter filter) {
    state = state.copyWith(filter: filter);
  }
}

class CarePlanDetailsState {
  const CarePlanDetailsState({
    this.isLoading = false,
    this.plan,
    this.days = const [],
    this.meals = const [],
    this.foods = const [],
    this.instructions = const [],
    this.checkins = const [],
    this.mealLogs = const [],
    this.progress,
    this.error,
  });

  final bool isLoading;
  final CarePlan? plan;
  final List<CarePlanDay> days;
  final List<CarePlanMeal> meals;
  final List<CarePlanFoodItem> foods;
  final List<CarePlanInstruction> instructions;
  final List<CarePlanCheckin> checkins;
  final List<MealLog> mealLogs;
  final CarePlanProgress? progress;
  final ApiError? error;

  bool get canTrack => plan?.isActive == true;

  CarePlanDetailsState copyWith({
    bool? isLoading,
    CarePlan? plan,
    List<CarePlanDay>? days,
    List<CarePlanMeal>? meals,
    List<CarePlanFoodItem>? foods,
    List<CarePlanInstruction>? instructions,
    List<CarePlanCheckin>? checkins,
    List<MealLog>? mealLogs,
    CarePlanProgress? progress,
    ApiError? error,
    bool clearError = false,
  }) {
    return CarePlanDetailsState(
      isLoading: isLoading ?? this.isLoading,
      plan: plan ?? this.plan,
      days: days ?? this.days,
      meals: meals ?? this.meals,
      foods: foods ?? this.foods,
      instructions: instructions ?? this.instructions,
      checkins: checkins ?? this.checkins,
      mealLogs: mealLogs ?? this.mealLogs,
      progress: progress ?? this.progress,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final carePlanDetailsControllerProvider = StateNotifierProvider.autoDispose
    .family<CarePlanDetailsController, CarePlanDetailsState, int>((
      ref,
      planId,
    ) {
      return CarePlanDetailsController(
        planId,
        ref.watch(carePlansRepositoryProvider),
      )..load();
    });

class CarePlanDetailsController extends StateNotifier<CarePlanDetailsState> {
  CarePlanDetailsController(this.planId, CarePlansRepository repository)
    : _getDetails = GetCarePlanDetails(repository),
      _getDays = GetCarePlanDays(repository),
      _getMeals = GetCarePlanMeals(repository),
      _getFoods = GetCarePlanFoods(repository),
      _getInstructions = GetCarePlanInstructions(repository),
      _getCheckins = GetCarePlanCheckins(repository),
      _getMealLogs = GetMealLogs(repository),
      _getProgress = GetCarePlanProgress(repository),
      super(const CarePlanDetailsState());

  final int planId;
  final GetCarePlanDetails _getDetails;
  final GetCarePlanDays _getDays;
  final GetCarePlanMeals _getMeals;
  final GetCarePlanFoods _getFoods;
  final GetCarePlanInstructions _getInstructions;
  final GetCarePlanCheckins _getCheckins;
  final GetMealLogs _getMealLogs;
  final GetCarePlanProgress _getProgress;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final planResult = await _getDetails(planId);
    final daysResult = await _getDays(planId);
    final mealsResult = await _getMeals(planId);
    final foodsResult = await _getFoods(planId);
    final instructionsResult = await _getInstructions(planId);
    final checkinsResult = await _getCheckins(planId);
    final mealLogsResult = await _getMealLogs(planId);
    final progressResult = await _getProgress(planId);

    state = planResult.when(
      success: (plan) => CarePlanDetailsState(
        isLoading: false,
        plan: plan,
        days: daysResult is ApiSuccess<List<CarePlanDay>>
            ? daysResult.data
            : const [],
        meals: mealsResult is ApiSuccess<List<CarePlanMeal>>
            ? mealsResult.data
            : const [],
        foods: foodsResult is ApiSuccess<List<CarePlanFoodItem>>
            ? foodsResult.data
            : const [],
        instructions:
            instructionsResult is ApiSuccess<List<CarePlanInstruction>>
            ? instructionsResult.data
            : const [],
        checkins: checkinsResult is ApiSuccess<List<CarePlanCheckin>>
            ? checkinsResult.data
            : const [],
        mealLogs: mealLogsResult is ApiSuccess<List<MealLog>>
            ? mealLogsResult.data
            : const [],
        progress: progressResult is ApiSuccess<CarePlanProgress>
            ? progressResult.data
            : null,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}

class CarePlanFormState {
  const CarePlanFormState({this.isSubmitting = false, this.error});

  final bool isSubmitting;
  final ApiError? error;

  CarePlanFormState copyWith({
    bool? isSubmitting,
    ApiError? error,
    bool clearError = false,
  }) {
    return CarePlanFormState(
      isSubmitting: isSubmitting ?? this.isSubmitting,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final carePlanCheckinControllerProvider =
    StateNotifierProvider.autoDispose<
      CarePlanCheckinController,
      CarePlanFormState
    >((ref) {
      return CarePlanCheckinController(ref.watch(carePlansRepositoryProvider));
    });

class CarePlanCheckinController extends StateNotifier<CarePlanFormState> {
  CarePlanCheckinController(CarePlansRepository repository)
    : _createCheckin = CreateCarePlanCheckin(repository),
      super(const CarePlanFormState());

  final CreateCarePlanCheckin _createCheckin;

  Future<CarePlanCheckin?> submit(
    int planId,
    CreateCarePlanCheckinRequest request,
  ) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _createCheckin(planId, request);
    return result.when(
      success: (checkin) {
        state = state.copyWith(isSubmitting: false, clearError: true);
        return checkin;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return null;
      },
    );
  }
}

final mealLogControllerProvider =
    StateNotifierProvider.autoDispose<MealLogController, CarePlanFormState>((
      ref,
    ) {
      return MealLogController(ref.watch(carePlansRepositoryProvider));
    });

class MealLogController extends StateNotifier<CarePlanFormState> {
  MealLogController(CarePlansRepository repository)
    : _createMealLog = CreateMealLog(repository),
      super(const CarePlanFormState());

  final CreateMealLog _createMealLog;

  Future<MealLog?> submit(int planId, CreateMealLogRequest request) async {
    state = state.copyWith(isSubmitting: true, clearError: true);
    final result = await _createMealLog(planId, request);
    return result.when(
      success: (log) {
        state = state.copyWith(isSubmitting: false, clearError: true);
        return log;
      },
      failure: (failure) {
        state = state.copyWith(isSubmitting: false, error: failure.error);
        return null;
      },
    );
  }
}

class CarePlanProgressState {
  const CarePlanProgressState({
    this.isLoading = false,
    this.progress,
    this.error,
  });

  final bool isLoading;
  final CarePlanProgress? progress;
  final ApiError? error;

  CarePlanProgressState copyWith({
    bool? isLoading,
    CarePlanProgress? progress,
    ApiError? error,
    bool clearError = false,
  }) {
    return CarePlanProgressState(
      isLoading: isLoading ?? this.isLoading,
      progress: progress ?? this.progress,
      error: clearError ? null : error ?? this.error,
    );
  }
}

final carePlanProgressControllerProvider = StateNotifierProvider.autoDispose
    .family<CarePlanProgressController, CarePlanProgressState, int>((
      ref,
      planId,
    ) {
      return CarePlanProgressController(
        planId,
        ref.watch(carePlansRepositoryProvider),
      )..load();
    });

class CarePlanProgressController extends StateNotifier<CarePlanProgressState> {
  CarePlanProgressController(this.planId, CarePlansRepository repository)
    : _getProgress = GetCarePlanProgress(repository),
      super(const CarePlanProgressState());

  final int planId;
  final GetCarePlanProgress _getProgress;

  Future<void> load() async {
    state = state.copyWith(isLoading: true, clearError: true);
    final result = await _getProgress(planId);
    state = result.when(
      success: (progress) => state.copyWith(
        isLoading: false,
        progress: progress,
        clearError: true,
      ),
      failure: (failure) =>
          state.copyWith(isLoading: false, error: failure.error),
    );
  }
}
