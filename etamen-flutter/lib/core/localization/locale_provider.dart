import 'dart:async';

import 'package:etamen_app/core/settings/app_settings_storage.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

final localeControllerProvider =
    StateNotifierProvider<LocaleController, Locale>((ref) {
      return LocaleController(ref.watch(appSettingsStorageProvider));
    });

final appSettingsStorageProvider = Provider<AppSettingsStorage>((ref) {
  return SecureAppSettingsStorage();
});

class LocaleController extends StateNotifier<Locale> {
  LocaleController(this._storage) : super(const Locale('ar')) {
    unawaited(loadSavedLocale());
  }

  final AppSettingsStorage _storage;

  Future<void> loadSavedLocale() async {
    final localeCode = await _storage.readLocaleCode();
    if (localeCode == 'ar' || localeCode == 'en') {
      state = Locale(localeCode!);
    }
  }

  Future<void> setLocale(Locale locale) async {
    final normalizedLocale = locale.languageCode == 'en'
        ? const Locale('en')
        : const Locale('ar');
    state = normalizedLocale;
    await _storage.saveLocaleCode(normalizedLocale.languageCode);
  }

  Future<void> toggle() {
    return setLocale(
      state.languageCode == 'ar' ? const Locale('en') : const Locale('ar'),
    );
  }
}
