class PharmacyPrescription {
  const PharmacyPrescription({
    required this.id,
    this.fileName,
    this.notes,
    this.createdAt,
  });

  final int id;
  final String? fileName;
  final String? notes;
  final DateTime? createdAt;
}
