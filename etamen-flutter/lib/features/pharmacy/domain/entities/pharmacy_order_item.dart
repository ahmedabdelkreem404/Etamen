class PharmacyOrderItem {
  const PharmacyOrderItem({
    required this.productName,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
    this.productId,
  });

  final int? productId;
  final String productName;
  final int quantity;
  final String unitPrice;
  final String lineTotal;
}
