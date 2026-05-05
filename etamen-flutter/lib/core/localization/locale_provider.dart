import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final localeControllerProvider =
    StateNotifierProvider<LocaleController, Locale>((ref) {
      return LocaleController();
    });

class LocaleController extends StateNotifier<Locale> {
  LocaleController() : super(const Locale('ar'));

  void setLocale(Locale locale) {
    state = locale;
  }

  void toggle() {
    state = state.languageCode == 'ar'
        ? const Locale('en')
        : const Locale('ar');
  }
}
