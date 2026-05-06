import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_checkin.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_meal.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_progress.dart';
import 'package:etamen_app/features/care_plans/domain/entities/meal_log.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_card.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_disclaimer_box.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/checkin_form.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/meal_log_form.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/progress_summary_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('CarePlanDisclaimerBox shows required safety text', (
    tester,
  ) async {
    await tester.pumpWidget(_wrap(const CarePlanDisclaimerBox()));
    await tester.pumpAndSettle();

    expect(
      find.textContaining('هذه الخطة للتنظيم والمتابعة فقط'),
      findsOneWidget,
    );
    expect(find.textContaining('لا تغيّر دواء'), findsOneWidget);
  });

  testWidgets('CarePlanCard displays status and plan type', (tester) async {
    await tester.pumpWidget(
      _wrap(
        CarePlanCard(
          plan: const CarePlan(
            id: 1,
            title: 'خطة متابعة',
            planType: CarePlanType.nutrition,
            status: CarePlanStatus.active,
            startDate: '2026-05-06',
          ),
          onTap: () {},
          onProgress: () {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('خطة متابعة'), findsOneWidget);
    expect(find.text('خطة تغذية'), findsOneWidget);
    expect(find.text('نشط'), findsOneWidget);
  });

  testWidgets('Checkin form shows follow-up labels', (tester) async {
    await tester.pumpWidget(
      _wrap(
        CheckinForm(
          commitmentScore: 70,
          energyLevel: 3,
          hungerLevel: 3,
          sleepQuality: 3,
          mood: CheckinMood.neutral,
          symptomsController: TextEditingController(),
          generalController: TextEditingController(),
          onCommitmentChanged: (_) {},
          onEnergyChanged: (_) {},
          onHungerChanged: (_) {},
          onSleepChanged: (_) {},
          onMoodChanged: (_) {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.textContaining('درجة الالتزام'), findsOneWidget);
    expect(find.textContaining('مستوى الطاقة'), findsOneWidget);
    expect(find.text('المزاج'), findsOneWidget);
  });

  testWidgets('Meal log form exposes safe meal status options', (tester) async {
    await tester.pumpWidget(
      _wrap(
        MealLogForm(
          meals: const [
            CarePlanMeal(id: 1, mealType: MealType.breakfast, title: 'إفطار'),
          ],
          selectedMealId: null,
          mealType: MealType.breakfast,
          status: MealLogStatus.followed,
          descriptionController: TextEditingController(),
          notesController: TextEditingController(),
          onMealChanged: (_) {},
          onMealTypeChanged: (_) {},
          onStatusChanged: (_) {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('حالة الوجبة'), findsOneWidget);
    expect(find.text('اتبعت الوجبة'), findsOneWidget);
  });

  testWidgets('ProgressSummaryCard uses commitment wording', (tester) async {
    await tester.pumpWidget(
      _wrap(
        const ProgressSummaryCard(
          progress: CarePlanProgress(
            planId: 1,
            checkinsCount: 2,
            mealLogsCount: 3,
            averageCommitmentScore: 80,
            adherencePercentage: 75,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('75%'), findsOneWidget);
    expect(find.textContaining('متابعة الالتزام فقط'), findsOneWidget);
  });
}

Widget _wrap(Widget child) {
  return MaterialApp(
    locale: const Locale('ar'),
    supportedLocales: AppLocalizations.supportedLocales,
    localizationsDelegates: const [
      AppLocalizations.delegate,
      GlobalMaterialLocalizations.delegate,
      GlobalWidgetsLocalizations.delegate,
      GlobalCupertinoLocalizations.delegate,
    ],
    theme: AppTheme.light,
    home: Directionality(
      textDirection: TextDirection.rtl,
      child: Scaffold(body: child),
    ),
  );
}
