import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_product.dart';

class PharmacyCartItem {
  const PharmacyCartItem({required this.product, required this.quantity});

  final PharmacyProduct product;
  final int quantity;

  PharmacyCartItem copyWith({int? quantity}) {
    return PharmacyCartItem(
      product: product,
      quantity: quantity ?? this.quantity,
    );
  }

  double get localLineTotal {
    final price = double.tryParse(product.price) ?? 0;
    return price * quantity;
  }
}
