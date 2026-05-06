import 'package:etamen_app/core/network/api_result.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_checkin_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_day_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_food_item_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_instruction_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_model.dart';
import 'package:etamen_app/features/care_plans/data/models/care_plan_progress_model.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_checkin_request.dart';
import 'package:etamen_app/features/care_plans/data/models/create_care_plan_request.dart';
import 'package:etamen_app/features/care_plans/data/models/create_meal_log_request.dart';
import 'package:etamen_app/features/care_plans/data/models/meal_log_model.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_day.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_food_item.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_instruction.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_progress.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/domain/repositories/care_plans_repository.dart';
import 'package:etamen_app/features/care_plans/presentation/providers/care_plans_providers.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('CarePlanModel parses nullable safe fields', () {
    final plan = CarePlanModel.fromJson({
      'id': 5,
      'title': 'Follow-up plan',
      'plan_type': 'nutrition',
      'status': 'active',
      'goal_text': 'Organize meals',
      'start_date': '2026-05-06',
      'visibility': 'provider_assigned',
      'source': 'provider_assigned',
      'checkins_count': 2,
      'meal_logs_count': 3,
    });

    expect(plan.id, 5);
    expect(plan.planType, CarePlanType.nutrition);
    expect(plan.status, CarePlanStatus.active);
    expect(plan.isActive, true);
    expect(plan.checkinsCount, 2);
  });

  test('CarePlanDayModel parses nested meals', () {
    final day = CarePlanDayModel.fromJson({
      'id': 1,
      'care_plan_id': 5,
      'day_number': 1,
      'title': 'Day one',
      'meals': [
        {
          'id': 7,
          'care_plan_day_id': 1,
          'meal_type': 'breakfast',
          'calories': 300,
          'is_required': true,
        },
      ],
    });

    expect(day.meals, hasLength(1));
    expect(day.meals.single.mealType, MealType.breakfast);
    expect(day.meals.single.isRequired, true);
  });

  test('Food and instruction models parse categories safely', () {
    final food = CarePlanFoodItemModel.fromJson({
      'id': 2,
      'category': 'forbidden',
      'name': 'Soda',
      'safety_note': 'Plan-specific guidance',
    });
    final instruction = CarePlanInstructionModel.fromJson({
      'id': 3,
      'instruction_type': 'hydration',
      'body': 'Drink water as agreed',
    });

    expect(food.category, FoodCategory.forbidden);
    expect(instruction.instructionType, InstructionType.hydration);
  });

  test('Checkin MealLog and Progress models parse tracking data', () {
    final checkin = CarePlanCheckinModel.fromJson({
      'id': 4,
      'care_plan_id': 5,
      'checkin_date': '2026-05-06',
      'commitment_score': 80,
      'mood': 'good',
    });
    final log = MealLogModel.fromJson({
      'id': 8,
      'care_plan_id': 5,
      'logged_at': '2026-05-06T10:00:00Z',
      'meal_type': 'lunch',
      'status': 'partially_followed',
    });
    final progress = CarePlanProgressModel.fromJson({
      'plan_id': 5,
      'checkins_count': 1,
      'meal_logs_count': 1,
      'average_commitment_score': '80',
      'adherence_percentage': '75',
      'latest_checkin': {'id': 4, 'care_plan_id': 5},
      'latest_meal_logs': [
        {'id': 8, 'care_plan_id': 5, 'status': 'followed'},
      ],
      'safe_disclaimer': 'tracking only',
    });

    expect(checkin.mood, CheckinMood.good);
    expect(log.status, MealLogStatus.partiallyFollowed);
    expect(progress.latestCheckin, isNotNull);
    expect(progress.latestMealLogs, hasLength(1));
    expect(progress.adherencePercentage, 75);
  });

  test('Care plan enum mappings tolerate unknown backend values', () {
    expect(CarePlanType.fromWire('general_care'), CarePlanType.generalCare);
    expect(CarePlanType.fromWire('bad'), CarePlanType.unknown);
    expect(MealType.fromWire('snack_2'), MealType.snack2);
    expect(FoodCategory.fromWire('limited'), FoodCategory.limited);
    expect(
      InstructionType.fromWire('provider_note'),
      InstructionType.providerNote,
    );
    expect(MealLogStatus.fromWire('extra_meal'), MealLogStatus.extraMeal);
    expect(CheckinMood.fromWire('very_bad'), CheckinMood.veryBad);
  });

  test('CreateCarePlanRequest excludes ownership and medical claim fields', () {
    final json = CreateCarePlanRequest(
      planType: CarePlanType.nutrition,
      title: 'My plan',
      startDate: '2026-05-06',
      description: 'Organize meals',
    ).toJson();

    expect(json['plan_type'], 'nutrition');
    expect(json.containsKey('patient_user_id'), false);
    expect(json.containsKey('assigned_by_user_id'), false);
    expect(json.containsKey('provider_id'), false);
    expect(json.containsKey('source'), false);
    expect(json.containsKey('visibility'), false);
    expect(json.containsKey('status'), false);
    expect(json.containsKey('diagnosis'), false);
    expect(json.containsKey('treatment'), false);
  });

  test('Checkin and meal log requests exclude forbidden fields', () {
    final checkin = const CreateCarePlanCheckinRequest(
      checkinDate: '2026-05-06',
      commitmentScore: 70,
      mood: CheckinMood.good,
    ).toJson();
    final mealLog = CreateMealLogRequest(
      loggedAt: DateTime.utc(2026, 5, 6, 10),
      status: MealLogStatus.followed,
      mealType: MealType.breakfast,
      description: 'Done',
    ).toJson();

    expect(checkin.containsKey('patient_user_id'), false);
    expect(checkin.containsKey('source'), false);
    expect(checkin.containsKey('status'), false);
    expect(checkin.containsKey('diagnosis'), false);
    expect(checkin.containsKey('treatment'), false);
    expect(mealLog.containsKey('patient_user_id'), false);
    expect(mealLog.containsKey('progress'), false);
    expect(mealLog.containsKey('adherence'), false);
    expect(mealLog.containsKey('calories'), false);
    expect(mealLog.containsKey('diagnosis'), false);
    expect(mealLog.containsKey('treatment'), false);
  });

  test('inactive plan disables check-in and meal-log logic', () {
    const plan = CarePlan(
      id: 1,
      title: 'Paused',
      planType: CarePlanType.generalCare,
      status: CarePlanStatus.paused,
    );

    expect(plan.isActive, false);
    expect(plan.isInactive, true);
  });

  test('controllers load list details checkin meal log and progress', () async {
    final repository = FakeCarePlansRepository();
    final list = CarePlansController(repository);
    final details = CarePlanDetailsController(1, repository);
    final checkin = CarePlanCheckinController(repository);
    final mealLog = MealLogController(repository);
    final progress = CarePlanProgressController(1, repository);

    await list.load();
    list.selectFilter(CarePlanFilter.active);
    await details.load();
    final checkinResult = await checkin.submit(
      1,
      const CreateCarePlanCheckinRequest(checkinDate: '2026-05-06'),
    );
    final mealLogResult = await mealLog.submit(
      1,
      CreateMealLogRequest(
        loggedAt: DateTime.utc(2026, 5, 6),
        status: MealLogStatus.followed,
      ),
    );
    await progress.load();

    expect(list.state.filteredItems, hasLength(1));
    expect(details.state.days, hasLength(1));
    expect(details.state.foods, hasLength(1));
    expect(checkinResult, isNotNull);
    expect(mealLogResult, isNotNull);
    expect(progress.state.progress?.checkinsCount, 1);
  });
}

class FakeCarePlansRepository implements CarePlansRepository {
  @override
  Future<ApiResult<CarePlan>> createCarePlan(CreateCarePlanRequest request) {
    return Future.value(ApiSuccess(_plan));
  }

  @override
  Future<ApiResult<CarePlanCheckin>> createCheckin(
    int planId,
    CreateCarePlanCheckinRequest request,
  ) {
    return Future.value(ApiSuccess(CarePlanCheckin(id: 1, carePlanId: planId)));
  }

  @override
  Future<ApiResult<MealLog>> createMealLog(
    int planId,
    CreateMealLogRequest request,
  ) {
    return Future.value(
      ApiSuccess(MealLog(id: 1, carePlanId: planId, status: request.status)),
    );
  }

  @override
  Future<ApiResult<List<CarePlanCheckin>>> getCheckins(int planId) {
    return Future.value(
      ApiSuccess([CarePlanCheckin(id: 1, carePlanId: planId)]),
    );
  }

  @override
  Future<ApiResult<CarePlan>> getCarePlan(int planId) {
    return Future.value(ApiSuccess(_plan));
  }

  @override
  Future<ApiResult<List<CarePlan>>> getCarePlans({CarePlanStatus? status}) {
    return Future.value(ApiSuccess([_plan, _pausedPlan]));
  }

  @override
  Future<ApiResult<List<CarePlanDay>>> getDays(int planId) {
    return Future.value(
      ApiSuccess([
        CarePlanDay(id: 1, carePlanId: planId, meals: const [_meal]),
      ]),
    );
  }

  @override
  Future<ApiResult<List<CarePlanFoodItem>>> getFoods(int planId) {
    return Future.value(
      const ApiSuccess([
        CarePlanFoodItem(id: 1, category: FoodCategory.allowed, name: 'Apple'),
      ]),
    );
  }

  @override
  Future<ApiResult<List<CarePlanInstruction>>> getInstructions(int planId) {
    return Future.value(
      const ApiSuccess([
        CarePlanInstruction(
          id: 1,
          instructionType: InstructionType.general,
          body: 'Follow up only',
        ),
      ]),
    );
  }

  @override
  Future<ApiResult<List<MealLog>>> getMealLogs(int planId) {
    return Future.value(
      ApiSuccess([
        MealLog(id: 1, carePlanId: planId, status: MealLogStatus.followed),
      ]),
    );
  }

  @override
  Future<ApiResult<List<CarePlanMeal>>> getMeals(int planId) {
    return Future.value(const ApiSuccess([_meal]));
  }

  @override
  Future<ApiResult<CarePlanProgress>> getProgress(int planId) {
    return Future.value(
      ApiSuccess(CarePlanProgress(planId: planId, checkinsCount: 1)),
    );
  }

  @override
  Future<ApiResult<List<CarePlan>>> getSummaryPlans() {
    return getCarePlans();
  }

  @override
  Future<ApiResult<CarePlanCheckin>> updateCheckin(
    int planId,
    int checkinId,
    CreateCarePlanCheckinRequest request,
  ) {
    return createCheckin(planId, request);
  }

  @override
  Future<ApiResult<MealLog>> updateMealLog(
    int planId,
    int logId,
    CreateMealLogRequest request,
  ) {
    return createMealLog(planId, request);
  }

  static const _plan = CarePlan(
    id: 1,
    title: 'Nutrition',
    planType: CarePlanType.nutrition,
    status: CarePlanStatus.active,
  );

  static const _pausedPlan = CarePlan(
    id: 2,
    title: 'Paused',
    planType: CarePlanType.generalCare,
    status: CarePlanStatus.paused,
  );

  static const _meal = CarePlanMeal(
    id: 1,
    mealType: MealType.breakfast,
    title: 'Breakfast',
  );
}
