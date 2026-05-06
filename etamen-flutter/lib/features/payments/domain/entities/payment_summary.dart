import 'package:etamen_app/features/payments/domain/entities/payment_status.dart';

class PaymentSummary {
  const PaymentSummary({
    required this.id,
    required this.amount,
    required this.currency,
    required this.status,
  });

  final int id;
  final String amount;
  final String currency;
  final PaymentStatusEnum status;
}
