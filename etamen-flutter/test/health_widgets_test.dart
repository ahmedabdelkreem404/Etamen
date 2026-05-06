import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/presentation/widgets/health_disclaimer_box.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_card.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_flag_chip.dart';
import 'package:etamen_app/features/health/presentation/widgets/vital_type_selector.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('HealthDisclaimerBox shows required safety text', (tester) async {
    await tester.pumpWidget(_wrap(const HealthDisclaimerBox()));
    await tester.pumpAndSettle();

    expect(
      find.textContaining('هذه البيانات للمتابعة والتنظيم فقط'),
      findsOneWidget,
    );
  });

  testWidgets('VitalFlagChip labels are non-diagnostic', (tester) async {
    await tester.pumpWidget(_wrap(const VitalFlagChip(flag: VitalFlag.high)));
    await tester.pumpAndSettle();

    expect(find.text('مرتفع'), findsOneWidget);
    expect(find.textContaining('تشخيص'), findsNothing);
  });

  testWidgets('VitalTypeSelector shows blood pressure and sugar choices', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        VitalTypeSelector(value: VitalType.bloodPressure, onChanged: (_) {}),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('ضغط الدم'), findsOneWidget);
    expect(find.text('سكر الدم'), findsOneWidget);
  });

  testWidgets('VitalCard displays blood pressure value and safe flag', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        const VitalCard(
          record: VitalRecord(
            id: 1,
            vitalType: VitalType.bloodPressure,
            value: '120',
            secondaryValue: '80',
            unit: 'mmHg',
            flag: VitalFlag.normal,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('120/80 mmHg'), findsOneWidget);
    expect(find.text('ضمن المتابعة'), findsOneWidget);
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
