import 'package:etamen_app/core/config/app_config.dart';
import 'package:etamen_app/core/legal/legal_document_type.dart';
import 'package:etamen_app/core/legal/legal_documents.dart';
import 'package:etamen_app/core/localization/locale_provider.dart';
import 'package:etamen_app/core/settings/app_settings_storage.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('legal documents exist for all required types', () {
    for (final type in LegalDocumentType.values) {
      final document = LegalDocuments.byType(type);
      expect(document.titleAr, isNotEmpty);
      expect(document.titleEn, isNotEmpty);
      expect(document.sectionsAr, isNotEmpty);
      expect(document.sectionsEn, isNotEmpty);
      expect(document.sectionsAr.first, LegalDocuments.draftNoticeAr);
      expect(document.sectionsEn.first, LegalDocuments.draftNoticeEn);
    }
  });

  test('legal documents include medical, AI, and refund warnings', () {
    final medical = LegalDocuments.byType(
      LegalDocumentType.medicalDisclaimer,
    ).sectionsAr.join(' ');
    final ai = LegalDocuments.byType(
      LegalDocumentType.aiDisclaimer,
    ).sectionsEn.join(' ');
    final refund = LegalDocuments.byType(
      LegalDocumentType.refundPolicy,
    ).sectionsEn.join(' ');

    expect(medical, contains('لا يغني عن الطبيب'));
    expect(ai, contains('The AI assistant is not a doctor'));
    expect(refund, contains('Manual payments require proof'));
    expect(refund, contains('backend only'));
  });

  test('language setting stores selected locale', () async {
    final storage = MemorySettingsStorage();
    final controller = LocaleController(storage);

    await controller.setLocale(const Locale('en'));

    expect(controller.state.languageCode, 'en');
    expect(storage.localeCode, 'en');

    storage.localeCode = 'ar';
    await controller.loadSavedLocale();

    expect(controller.state.languageCode, 'ar');
  });

  test('AppConfig support placeholders parse safely', () {
    expect(AppConfig.supportEmail, isA<String>());
    expect(AppConfig.supportPhone, isA<String>());
    expect(AppConfig.supportWhatsappUrl, isA<String>());
    expect(AppConfig.appVersion, isNotEmpty);
    expect(AppConfig.buildNumber, isNotEmpty);
  });
}

class MemorySettingsStorage implements AppSettingsStorage {
  String? localeCode;

  @override
  Future<String?> readLocaleCode() async => localeCode;

  @override
  Future<void> saveLocaleCode(String localeCode) async {
    this.localeCode = localeCode;
  }
}
