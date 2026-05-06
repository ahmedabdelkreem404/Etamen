import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_cart_item.dart';

class CreatePharmacyOrderRequest {
  const CreatePharmacyOrderRequest({
    required this.pharmacyProviderId,
    required this.items,
    this.prescriptionId,
    this.notes,
    this.deliveryAddress,
    this.deliveryMethod = 'pickup',
  });

  final int pharmacyProviderId;
  final List<PharmacyCartItem> items;
  final int? prescriptionId;
  final String? notes;
  final String? deliveryAddress;
  final String? deliveryMethod;

  Map<String, dynamic> toJson() {
    return {
      'pharmacy_provider_id': pharmacyProviderId,
      'items': items
          .map(
            (item) => {
              'product_id': item.product.id,
              'quantity': item.quantity,
            },
          )
          .toList(growable: false),
      if (prescriptionId != null) 'prescription_id': prescriptionId,
      if (notes?.trim().isNotEmpty == true) 'notes': notes!.trim(),
      if (deliveryAddress?.trim().isNotEmpty == true)
        'delivery_address': deliveryAddress!.trim(),
      if (deliveryMethod?.trim().isNotEmpty == true)
        'delivery_method': deliveryMethod!.trim(),
    };
  }
}
