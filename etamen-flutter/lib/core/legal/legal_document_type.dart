enum LegalDocumentType {
  privacyPolicy,
  termsConditions,
  medicalDisclaimer,
  aiDisclaimer,
  refundPolicy,
}

extension LegalDocumentTypePath on LegalDocumentType {
  String get pathSegment {
    return switch (this) {
      LegalDocumentType.privacyPolicy => 'privacy',
      LegalDocumentType.termsConditions => 'terms',
      LegalDocumentType.medicalDisclaimer => 'medical-disclaimer',
      LegalDocumentType.aiDisclaimer => 'ai-disclaimer',
      LegalDocumentType.refundPolicy => 'refund-policy',
    };
  }
}
