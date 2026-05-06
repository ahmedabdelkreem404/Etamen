import 'package:etamen_app/features/pharmacy/domain/entities/pharmacy_order.dart';

class PharmacyOrderPayment {
  const PharmacyOrderPayment({required this.order, this.paymentId});

  final PharmacyOrder order;
  final int? paymentId;
}
