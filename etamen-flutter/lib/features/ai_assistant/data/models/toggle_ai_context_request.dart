class ToggleAiContextRequest {
  const ToggleAiContextRequest({required this.contextEnabled});

  final bool contextEnabled;

  Map<String, dynamic> toJson() {
    // Current backend request uses "enabled" for this toggle endpoint.
    return {'enabled': contextEnabled};
  }
}
