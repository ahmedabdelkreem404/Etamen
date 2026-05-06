import 'package:etamen_app/features/care_plans/domain/entities/care_plan.dart';

class CreateCarePlanRequest {
  const CreateCarePlanRequest({
    required this.planType,
    required this.title,
    required this.startDate,
    this.description,
    this.goalText,
    this.endDate,
    this.notes,
  });

  final CarePlanType planType;
  final String title;
  final String startDate;
  final String? description;
  final String? goalText;
  final String? endDate;
  final String? notes;

  Map<String, dynamic> toJson() {
    return {
      'plan_type': planType.wireValue,
      'title': title.trim(),
      'start_date': startDate,
      if (description?.trim().isNotEmpty == true)
        'description': description!.trim(),
      if (goalText?.trim().isNotEmpty == true) 'goal_text': goalText!.trim(),
      if (endDate?.trim().isNotEmpty == true) 'end_date': endDate,
      if (notes?.trim().isNotEmpty == true) 'notes': notes!.trim(),
    };
  }
}
