import 'package:etamen_app/features/care_plans/domain/entities/care_plan_instruction.dart';

class CarePlanInstructionModel extends CarePlanInstruction {
  const CarePlanInstructionModel({
    required super.id,
    required super.instructionType,
    required super.body,
    super.title,
    super.sortOrder,
  });

  factory CarePlanInstructionModel.fromJson(Map<String, dynamic> json) {
    return CarePlanInstructionModel(
      id: _toInt(json['id']) ?? 0,
      instructionType: InstructionType.fromWire(json['instruction_type']),
      title: _string(json['title']),
      body: _string(json['body']) ?? '',
      sortOrder: _toInt(json['sort_order']),
    );
  }
}

int? _toInt(Object? value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString());
}

String? _string(Object? value) {
  if (value == null) return null;
  final text = value.toString();
  return text.isEmpty ? null : text;
}
