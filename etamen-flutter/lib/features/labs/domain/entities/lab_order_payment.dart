import 'package:etamen_app/features/labs/domain/entities/lab_order.dart';

class LabOrderPayment {
  const LabOrderPayment({required this.order, this.paymentId});

  final LabOrder order;
  final int? paymentId;
}
