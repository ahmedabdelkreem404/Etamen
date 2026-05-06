class UploadPaymentProofRequest {
  const UploadPaymentProofRequest({
    required this.filePath,
    this.fileName,
    this.referenceNumber,
    this.senderPhone,
    this.notes,
  });

  final String filePath;
  final String? fileName;
  final String? referenceNumber;
  final String? senderPhone;
  final String? notes;

  static const int maxLocalBytes = 5 * 1024 * 1024;
}
