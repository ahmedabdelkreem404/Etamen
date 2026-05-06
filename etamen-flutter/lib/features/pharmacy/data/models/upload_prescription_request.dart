class UploadPrescriptionRequest {
  const UploadPrescriptionRequest({
    required this.filePath,
    this.fileName,
    this.pharmacyId,
    this.notes,
  });

  final String filePath;
  final String? fileName;
  final int? pharmacyId;
  final String? notes;

  static const int maxLocalBytes = 5 * 1024 * 1024;
}
