class PaymobSession {
  const PaymobSession({
    this.checkoutUrl,
    this.clientSecret,
    this.gatewayReference,
  });

  final String? checkoutUrl;
  final String? clientSecret;
  final String? gatewayReference;

  bool get hasCheckoutUrl => checkoutUrl != null && checkoutUrl!.isNotEmpty;
}
