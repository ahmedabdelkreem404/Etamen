class RadiologyResult {
  const RadiologyResult({
    required this.id,
    required this.orderId,
    required this.resultType,
    this.titleAr,
    this.titleEn,
    this.notesAr,
    this.notesEn,
    this.isVisibleToPatient = false,
    this.fileName,
    this.mimeType,
    this.fileSize,
    this.downloadUrl,
    this.uploadedAt,
  });

  final int id;
  final int orderId;
  final String resultType;
  final String? titleAr;
  final String? titleEn;
  final String? notesAr;
  final String? notesEn;
  final bool isVisibleToPatient;
  final String? fileName;
  final String? mimeType;
  final int? fileSize;
  final String? downloadUrl;
  final DateTime? uploadedAt;

  String title(bool isArabic) {
    if (!isArabic && titleEn?.trim().isNotEmpty == true) return titleEn!.trim();
    if (titleAr?.trim().isNotEmpty == true) return titleAr!.trim();
    if (fileName?.trim().isNotEmpty == true) return fileName!.trim();
    return isArabic ? 'نتيجة الأشعة' : 'Radiology result';
  }

  String? notes(bool isArabic) {
    if (!isArabic && notesEn?.trim().isNotEmpty == true) {
      return notesEn!.trim();
    }
    return notesAr;
  }
}

class RadiologyResultDownload {
  const RadiologyResultDownload({
    required this.resultId,
    required this.localPath,
  });

  final int resultId;
  final String localPath;
}
