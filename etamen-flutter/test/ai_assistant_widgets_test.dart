import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_context_toggle.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_disclaimer_box.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_message_bubble.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_provider_unavailable_view.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_safety_banner.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets('AiDisclaimerBox appears with required safety wording', (
    tester,
  ) async {
    await tester.pumpWidget(_wrap(const AiDisclaimerBox()));
    await tester.pumpAndSettle();

    expect(find.textContaining('AI assistant is not a doctor'), findsWidgets);
    expect(find.textContaining('does not provide diagnosis'), findsOneWidget);
    expect(
      find.textContaining('emergency services immediately'),
      findsOneWidget,
    );
  });

  testWidgets('AiMessageBubble renders user and assistant messages', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        Column(
          children: const [
            AiMessageBubble(
              message: AiMessage(
                id: 1,
                conversationId: 1,
                role: AiMessageRole.user,
                content: 'Help me organize symptoms',
                safetyClassification: AiSafetyClassification.safe,
              ),
            ),
            AiMessageBubble(
              message: AiMessage(
                id: 2,
                conversationId: 1,
                role: AiMessageRole.assistant,
                content: 'I can help organize information.',
                safetyClassification: AiSafetyClassification.safe,
              ),
            ),
          ],
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Help me organize symptoms'), findsOneWidget);
    expect(find.text('I can help organize information.'), findsOneWidget);
  });

  testWidgets('AiSafetyBanner displays refusal and emergency states', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        const Column(
          children: [
            AiSafetyBanner(isEmergency: false),
            AiSafetyBanner(isEmergency: true),
          ],
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.textContaining('cannot provide diagnosis'), findsOneWidget);
    expect(find.textContaining('Contact emergency services'), findsOneWidget);
  });

  testWidgets('emergency message bubble shows emergency banner', (
    tester,
  ) async {
    await tester.pumpWidget(
      _wrap(
        const AiMessageBubble(
          message: AiMessage(
            id: 3,
            conversationId: 1,
            role: AiMessageRole.assistant,
            content: 'Please contact emergency services.',
            safetyClassification: AiSafetyClassification.emergencyRedFlag,
          ),
        ),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.textContaining('Contact emergency services'), findsOneWidget);
  });

  testWidgets('AiContextToggle is visible and calls onChanged', (tester) async {
    var value = true;
    await tester.pumpWidget(
      _wrap(
        AiContextToggle(value: true, onChanged: (enabled) => value = enabled),
      ),
    );
    await tester.pumpAndSettle();

    expect(find.text('Health context'), findsOneWidget);
    await tester.tap(find.byType(Switch));
    await tester.pump();
    expect(value, false);
  });

  testWidgets('AiProviderUnavailableView displays safe temporary message', (
    tester,
  ) async {
    await tester.pumpWidget(_wrap(const AiProviderUnavailableView()));
    await tester.pumpAndSettle();

    expect(find.textContaining('temporarily unavailable'), findsOneWidget);
  });
}

Widget _wrap(Widget child) {
  return MaterialApp(
    locale: const Locale('en'),
    supportedLocales: AppLocalizations.supportedLocales,
    localizationsDelegates: const [
      AppLocalizations.delegate,
      GlobalMaterialLocalizations.delegate,
      GlobalWidgetsLocalizations.delegate,
      GlobalCupertinoLocalizations.delegate,
    ],
    theme: AppTheme.light,
    home: Scaffold(body: Center(child: child)),
  );
}
