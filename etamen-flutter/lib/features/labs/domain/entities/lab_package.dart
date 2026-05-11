import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';

class LabPackage {
  const LabPackage({
    required this.id,
    required this.name,
    required this.price,
    required this.currency,
    required this.isActive,
    this.labId,
    this.description,
    this.tests = const [],
    this.sampleTypes = const [],
    this.resultTimeHours,
  });

  final int id;
  final int? labId;
  final String name;
  final String? description;
  final String price;
  final String currency;
  final List<LabTest> tests;
  final List<String> sampleTypes;
  final int? resultTimeHours;
  final bool isActive;
}
