import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/fitness/domain/entities/fitness_entities.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';

String fitnessMoney(String? value, {String currency = 'EGP'}) {
  final amount = value?.trim();
  if (amount == null || amount.isEmpty) return '-';
  return '$amount $currency';
}

String coachTypeLabel(BuildContext context, String? type) {
  final isArabic = AppLocalizations.of(context).isArabic;
  return switch (type) {
    'fitness' || 'fitness_coach' => isArabic ? 'كوتش لياقة' : 'Fitness coach',
    'nutrition' ||
    'nutrition_coach' => isArabic ? 'كوتش تغذية' : 'Nutrition coach',
    'rehab' => isArabic ? 'تأهيل حركي' : 'Rehab fitness',
    'bodybuilding' => isArabic ? 'كمال أجسام' : 'Bodybuilding',
    'sports_performance' => isArabic ? 'أداء رياضي' : 'Sports performance',
    _ => isArabic ? 'كوتش' : 'Coach',
  };
}

String sessionModeLabel(BuildContext context, String mode) {
  final isArabic = AppLocalizations.of(context).isArabic;
  return switch (mode) {
    'online' => isArabic ? 'أونلاين' : 'Online',
    'home' => isArabic ? 'زيارة منزلية' : 'Home',
    'gym' => isArabic ? 'داخل الجيم' : 'Gym',
    _ => mode,
  };
}

String dateTimeLabel(DateTime? value) {
  if (value == null) return '-';
  final local = value.toLocal();
  final date =
      '${local.year}-${local.month.toString().padLeft(2, '0')}-${local.day.toString().padLeft(2, '0')}';
  final time =
      '${local.hour.toString().padLeft(2, '0')}:${local.minute.toString().padLeft(2, '0')}';
  return '$date $time';
}

class FitnessStatusChip extends StatelessWidget {
  const FitnessStatusChip({required this.label, super.key});

  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: AppColors.border),
      ),
      child: Text(
        label,
        style: Theme.of(context).textTheme.labelMedium?.copyWith(
          color: AppColors.primaryDark,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}

class FitnessBadge extends StatelessWidget {
  const FitnessBadge({required this.label, this.icon, super.key});

  final String label;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: AppColors.medicalAccentSoft,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 15, color: AppColors.primaryDark),
            const SizedBox(width: 5),
          ],
          Text(
            label,
            style: Theme.of(context).textTheme.labelMedium?.copyWith(
              color: AppColors.primaryDark,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }
}

class GymCard extends StatelessWidget {
  const GymCard({required this.gym, required this.onTap, super.key});

  final Gym gym;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              _IconBox(icon: Icons.fitness_center_outlined),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      gym.name(isArabic),
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    if (gym.locationLabel.isNotEmpty) ...[
                      const SizedBox(height: 4),
                      Text(
                        gym.locationLabel,
                        style: const TextStyle(color: AppColors.muted),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              FitnessBadge(
                label: uxCopy(
                  context,
                  '${gym.membershipPlansCount} خطة',
                  '${gym.membershipPlansCount} plans',
                ),
                icon: Icons.card_membership_outlined,
              ),
              FitnessBadge(
                label: uxCopy(
                  context,
                  '${gym.classesCount} حصة',
                  '${gym.classesCount} classes',
                ),
                icon: Icons.event_available_outlined,
              ),
              if (gym.hasPersonalTraining)
                FitnessBadge(
                  label: uxCopy(context, 'تدريب شخصي', 'Personal training'),
                  icon: Icons.person_outline,
                ),
            ],
          ),
          const SizedBox(height: 12),
          FilledButton.icon(
            onPressed: onTap,
            icon: const Icon(Icons.arrow_forward, size: 16),
            label: Text(uxCopy(context, 'التفاصيل', 'Details')),
          ),
        ],
      ),
    );
  }
}

class CoachCard extends StatelessWidget {
  const CoachCard({required this.coach, required this.onTap, super.key});

  final Coach coach;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final isArabic = AppLocalizations.of(context).isArabic;
    return SoftMedicalCard(
      margin: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              _IconBox(icon: Icons.sports_handball_outlined),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      coach.name(isArabic),
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      coachTypeLabel(context, coach.coachType ?? coach.type),
                      style: const TextStyle(color: AppColors.muted),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              FitnessBadge(
                label: uxCopy(
                  context,
                  '${coach.sessionTypesCount} جلسة',
                  '${coach.sessionTypesCount} sessions',
                ),
                icon: Icons.schedule_outlined,
              ),
              FitnessBadge(
                label: uxCopy(
                  context,
                  '${coach.availabilityCount} موعد',
                  '${coach.availabilityCount} slots',
                ),
                icon: Icons.event_available_outlined,
              ),
              if (coach.onlineCoachingEnabled)
                FitnessBadge(
                  label: uxCopy(context, 'أونلاين', 'Online'),
                  icon: Icons.videocam_outlined,
                ),
            ],
          ),
          const SizedBox(height: 12),
          FilledButton.icon(
            onPressed: onTap,
            icon: const Icon(Icons.arrow_forward, size: 16),
            label: Text(uxCopy(context, 'التفاصيل', 'Details')),
          ),
        ],
      ),
    );
  }
}

class FitnessInfoLine extends StatelessWidget {
  const FitnessInfoLine({required this.label, required this.value, super.key});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 8),
      child: Row(
        children: [
          Expanded(child: Text(label)),
          const SizedBox(width: 12),
          Flexible(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: const TextStyle(fontWeight: FontWeight.w800),
            ),
          ),
        ],
      ),
    );
  }
}

class FitnessBookingPaymentCard extends StatelessWidget {
  const FitnessBookingPaymentCard({
    required this.amount,
    required this.statusLabel,
    required this.onPay,
    this.paymentId,
    super.key,
  });

  final String amount;
  final String statusLabel;
  final int? paymentId;
  final VoidCallback? onPay;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              _IconBox(icon: Icons.payments_outlined, small: true),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  uxCopy(context, 'ملخص الدفع', 'Payment summary'),
                  style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              FitnessStatusChip(label: statusLabel),
            ],
          ),
          const SizedBox(height: 12),
          Text(
            amount,
            style: Theme.of(
              context,
            ).textTheme.headlineSmall?.copyWith(fontWeight: FontWeight.w900),
          ),
          const SizedBox(height: 8),
          Text(
            uxCopy(
              context,
              'السعر النهائي والدفع يتأكدان من النظام فقط.',
              'Final price and payment are verified by the backend.',
            ),
            style: const TextStyle(color: AppColors.muted),
          ),
          if (paymentId != null && onPay != null) ...[
            const SizedBox(height: 14),
            FilledButton.icon(
              onPressed: onPay,
              icon: const Icon(Icons.payment_outlined, size: 16),
              label: Text(
                uxCopy(context, 'اختيار طريقة الدفع', 'Choose payment'),
              ),
            ),
          ],
        ],
      ),
    );
  }
}

class _IconBox extends StatelessWidget {
  const _IconBox({required this.icon, this.small = false});

  final IconData icon;
  final bool small;

  @override
  Widget build(BuildContext context) {
    final size = small ? 40.0 : 48.0;
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(14),
      ),
      child: Icon(icon, color: AppColors.primaryDark),
    );
  }
}
