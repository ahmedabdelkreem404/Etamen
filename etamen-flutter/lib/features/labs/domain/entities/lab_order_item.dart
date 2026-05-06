class LabOrderItem {
  const LabOrderItem({
    required this.itemType,
    required this.itemName,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
    this.testId,
    this.packageId,
  });

  final String itemType;
  final int? testId;
  final int? packageId;
  final String itemName;
  final int quantity;
  final String unitPrice;
  final String lineTotal;
}
