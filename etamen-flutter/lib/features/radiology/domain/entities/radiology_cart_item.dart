import 'package:etamen_app/features/radiology/domain/entities/radiology_scan.dart';

class RadiologyCartItem {
  const RadiologyCartItem({required this.scan, this.quantity = 1});

  final RadiologyScan scan;
  final int quantity;

  int get scanId => scan.id;

  double get localLineTotal => scan.priceValue * quantity;

  RadiologyCartItem copyWith({int? quantity}) {
    return RadiologyCartItem(scan: scan, quantity: quantity ?? this.quantity);
  }
}
