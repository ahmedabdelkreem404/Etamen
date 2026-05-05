import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/router.dart';
import 'package:etamen_app/app/theme/app_theme.dart';
import 'package:etamen_app/core/localization/locale_provider.dart';
import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

class EtamenApp extends ConsumerWidget {
  const EtamenApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(appRouterProvider);
    final locale = ref.watch(localeControllerProvider);

    return MaterialApp.router(
      title: 'Etamen',
      debugShowCheckedModeBanner: false,
      routerConfig: router,
      theme: AppTheme.light,
      locale: locale,
      supportedLocales: AppLocalizations.supportedLocales,
      localizationsDelegates: const [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
      ],
      builder: (context, child) {
        return Directionality(
          textDirection: Directionality.of(context),
          child: child ?? const SizedBox.shrink(),
        );
      },
    );
  }
}
