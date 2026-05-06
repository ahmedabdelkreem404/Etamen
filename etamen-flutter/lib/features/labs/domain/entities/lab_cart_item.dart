import 'package:etamen_app/features/labs/domain/entities/lab_package.dart';
import 'package:etamen_app/features/labs/domain/entities/lab_test.dart';

enum LabCartItemType { test, package }

class LabCartItem {
  const LabCartItem({
    required this.type,
    required this.quantity,
    this.test,
    this.package,
  });

  final LabCartItemType type;
  final LabTest? test;
  final LabPackage? package;
  final int quantity;

  int get itemId => type == LabCartItemType.test ? test!.id : package!.id;

  String get name => type == LabCartItemType.test ? test!.name : package!.name;

  String get price =>
      type == LabCartItemType.test ? test!.price : package!.price;

  String get currency =>
      type == LabCartItemType.test ? test!.currency : package!.currency;

  int? get labId => type == LabCartItemType.test ? test!.labId : package!.labId;

  double get localLineTotal => (double.tryParse(price) ?? 0) * quantity;

  LabCartItem copyWith({int? quantity}) {
    return LabCartItem(
      type: type,
      test: test,
      package: package,
      quantity: quantity ?? this.quantity,
    );
  }
}
