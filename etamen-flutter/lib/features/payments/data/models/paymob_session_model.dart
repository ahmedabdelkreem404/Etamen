import 'package:etamen_app/features/payments/domain/entities/paymob_session.dart';

class PaymobSessionModel extends PaymobSession {
  const PaymobSessionModel({
    super.checkoutUrl,
    super.clientSecret,
    super.gatewayReference,
  });

  factory PaymobSessionModel.fromJson(Map<String, dynamic> json) {
    return PaymobSessionModel(
      checkoutUrl: (json['checkout_url'] ?? json['url'])?.toString(),
      clientSecret: json['client_secret']?.toString(),
      gatewayReference: (json['gateway_reference'] ?? json['reference'])
          ?.toString(),
    );
  }
}
