import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/ai_assistant/domain/entities/ai_message.dart';
import 'package:etamen_app/features/ai_assistant/presentation/widgets/ai_safety_banner.dart';
import 'package:flutter/material.dart';

class AiMessageBubble extends StatelessWidget {
  const AiMessageBubble({required this.message, super.key});

  final AiMessage message;

  @override
  Widget build(BuildContext context) {
    final isUser = message.isUser;
    final color = isUser ? AppColors.primary : AppColors.surface;
    final textColor = isUser ? Colors.white : AppColors.text;

    return Align(
      alignment: isUser
          ? AlignmentDirectional.centerEnd
          : AlignmentDirectional.centerStart,
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 340),
        child: DecoratedBox(
          decoration: BoxDecoration(
            color: color,
            borderRadius: BorderRadius.circular(10),
            border: isUser ? null : Border.all(color: AppColors.border),
          ),
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (message.isEmergency || message.isRefusal) ...[
                  AiSafetyBanner(
                    isEmergency: message.isEmergency,
                    message: message.isEmergency ? null : null,
                  ),
                  const SizedBox(height: 8),
                ],
                Text(message.content, style: TextStyle(color: textColor)),
                if (message.createdAt != null) ...[
                  const SizedBox(height: 6),
                  Text(
                    message.createdAt!.toLocal().toString(),
                    style: TextStyle(
                      color: isUser
                          ? Colors.white.withValues(alpha: 0.75)
                          : AppColors.muted,
                      fontSize: 11,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}
