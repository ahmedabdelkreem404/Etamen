import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_adherence.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_reminder_time.dart';
import 'package:etamen_app/features/medications/domain/entities/medication_schedule_item.dart';
import 'package:etamen_app/features/medications/presentation/widgets/adherence_summary_card.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_disclaimer_box.dart';
import 'package:etamen_app/features/medications/presentation/widgets/medication_reminder_card.dart';
import 'package:etamen_app/features/medications/presentation/widgets/today_medication_card.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('MedicationDisclaimerBox shows required safety wording', (
    tester,
  ) async {
    await tester.pumpWidget(_wrap(const MedicationDisclaimerBox()));
    await tester.pumpAndSettle();

    expect(find.textContaining('تذكيرات الأدوية للتنظيم فقط'), findsOneWidget);
    expect(find.textContaining('لا توقف أو تغير جرعة'), findsOneWidget);
  });

  testWidgets('MedicationReminderCard displays status and reminder time', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        MedicationReminderCard(
          reminder: const MedicationReminder(
            id: 1,
            medicationName: 'Vitamin D',
            dosage: '1000',
            dosageUnit: 'IU',
            frequencyType: MedicationFrequencyType.onceDaily,
            status: MedicationReminderStatus.active,
            times: [
              MedicationReminderTime(
                id: 1,
                medicationReminderId: 1,
                timeOfDay: '08:00',
              ),
            ],
          ),
          onTap: () {},
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Vitamin D'), findsOneWidget);
    expect(find.text('1000 IU'), findsOneWidget);
    expect(find.text('08:00'), findsOneWidget);
    expect(find.text('نشط'), findsOneWidget);
  });

  testWidgets('TodayMedicationCard exposes taken and skipped actions', (
    tester,
  ) async {
    var taken = false;
    var skipped = false;

    await tester.pumpWidget(
      _wrap(
        TodayMedicationCard(
          item: MedicationScheduleItem(
            reminderId: 1,
            medicationName: 'Morning med',
            scheduledFor: DateTime.utc(2026, 5, 6, 8),
          ),
          onTaken: () => taken = true,
          onSkipped: () => skipped = true,
        ),
      ),
    );
    await tester.pumpAndSettle();

    await tester.tap(find.text('تسجيل كأُخذت'));
    await tester.pump();
    await tester.tap(find.text('تسجيل كتخطي'));
    await tester.pump();

    expect(taken, true);
    expect(skipped, true);
  });

  testWidgets('AdherenceSummaryCard displays organization counts', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        const AdherenceSummaryCard(
          adherence: MedicationAdherence(
            totalScheduled: 12,
            takenCount: 9,
            skippedCount: 2,
            missedCount: 1,
            adherencePercentage: 75,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('75%'), findsOneWidget);
    expect(find.text('12'), findsOneWidget);
    expect(find.text('9'), findsOneWidget);
    expect(find.textContaining('المتابعة فقط'), findsOneWidget);
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
