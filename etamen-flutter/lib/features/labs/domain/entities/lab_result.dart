class LabResult {
  const LabResult({
    required this.id,
    this.labOrderId,
    this.fileName,
    this.status,
    this.uploadedAt,
    this.notes,
  });

  final int id;
  final int? labOrderId;
  final String? fileName;
  final String? status;
  final DateTime? uploadedAt;
  final String? notes;
}

class LabResultDownload {
  const LabResultDownload({required this.resultId, required this.localPath});

  final int resultId;
  final String localPath;
}
