class CarePlanInstruction {
  const CarePlanInstruction({
    required this.id,
    required this.instructionType,
    required this.body,
    this.title,
    this.sortOrder,
  });

  final int id;
  final InstructionType instructionType;
  final String? title;
  final String body;
  final int? sortOrder;
}

enum InstructionType {
  general('general'),
  hydration('hydration'),
  sleep('sleep'),
  activity('activity'),
  nutrition('nutrition'),
  warning('warning'),
  providerNote('provider_note'),
  unknown('unknown');

  const InstructionType(this.wireValue);

  final String wireValue;

  static InstructionType fromWire(Object? value) {
    final normalized = value?.toString();
    return InstructionType.values.firstWhere(
      (item) => item.wireValue == normalized,
      orElse: () => InstructionType.unknown,
    );
  }
}
