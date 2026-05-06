import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/care_plans/domain/entities/care_plan_instruction.dart';
import 'package:etamen_app/features/care_plans/presentation/widgets/care_plan_labels.dart';
import 'package:flutter/material.dart';

class InstructionCard extends StatelessWidget {
  const InstructionCard({required this.instruction, super.key});

  final CarePlanInstruction instruction;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              instruction.title ??
                  instructionTypeLabel(context, instruction.instructionType),
              style: Theme.of(context).textTheme.titleSmall,
            ),
            const SizedBox(height: 6),
            Text(instruction.body),
            const SizedBox(height: 4),
            Text(
              instructionTypeLabel(context, instruction.instructionType),
              style: const TextStyle(color: AppColors.muted, fontSize: 12),
            ),
          ],
        ),
      ),
    );
  }
}
