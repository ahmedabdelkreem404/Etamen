import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/presentation/providers/doctors_providers.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/slot_picker.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class DoctorProfilePage extends ConsumerWidget {
  const DoctorProfilePage({required this.doctorId, super.key});

  final int doctorId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final details = ref.watch(doctorDetailsProvider(doctorId));
    final slots = ref.watch(doctorSlotsProvider(doctorId));

    return AppScaffold(
      title: uxCopy(context, 'تفاصيل الدكتور', 'Doctor details'),
      body: details.when(
        loading: () => const LoadingView(),
        error: (error, _) => ErrorView(message: error.toString()),
        data: (result) => result.when(
          success: (doctor) => ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _DoctorHero(doctor: doctor),
              const SizedBox(height: 14),
              SoftMedicalCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      uxCopy(context, 'نبذة عن الطبيب', 'About the doctor'),
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      doctor.bio == null || doctor.bio!.trim().isEmpty
                          ? uxCopy(
                              context,
                              'لم تُضف نبذة الطبيب بعد. يمكنك مراجعة التخصص والمكان والمواعيد المتاحة قبل الحجز.',
                              'The doctor bio has not been added yet. Review specialty, location, and slots before booking.',
                            )
                          : doctor.bio!,
                      style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        color: AppColors.softText,
                        height: 1.45,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 14),
              SoftMedicalCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    HomeSectionHeader(
                      title: uxCopy(
                        context,
                        'المواعيد المتاحة',
                        'Available slots',
                      ),
                      subtitle: uxCopy(
                        context,
                        'اختار من صفحة الحجز بعد مراجعة بيانات الدكتور.',
                        'Choose a slot on the booking step.',
                      ),
                    ),
                    const SizedBox(height: 12),
                    slots.when(
                      loading: () => const LoadingView(),
                      error: (error, _) => ErrorView(message: error.toString()),
                      data: (slotResult) => slotResult.when(
                        success: (items) => SlotPicker(
                          slots: items
                              .where((slot) => slot.isAvailable)
                              .toList(),
                          selectedSlot: null,
                          onSelected: (_) {},
                        ),
                        failure: (failure) =>
                            ErrorView(message: failure.error.message),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 18),
              AppButton(
                label: uxCopy(
                  context,
                  'احجز موعد مع الدكتور',
                  'Book this doctor',
                ),
                onPressed: doctor.doctorProfileId == null
                    ? null
                    : () => context.go(RouteNames.doctorBooking(doctor.id)),
              ),
              const SizedBox(height: 8),
              Text(
                uxCopy(
                  context,
                  'التكلفة وحالة الموعد يتم تأكيدهما من النظام بعد الحجز.',
                  'Fee and appointment state are confirmed by the system.',
                ),
                textAlign: TextAlign.center,
                style: Theme.of(
                  context,
                ).textTheme.bodySmall?.copyWith(color: AppColors.muted),
              ),
            ],
          ),
          failure: (failure) => ErrorView(message: failure.error.message),
        ),
      ),
    );
  }
}

class _DoctorHero extends StatelessWidget {
  const _DoctorHero({required this.doctor});

  final Doctor doctor;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppColors.primaryDark, AppColors.primary, AppColors.cyan],
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.22),
            blurRadius: 22,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              DoctorAvatar(name: doctor.name, size: 76),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      doctor.name,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    if (doctor.specialties.isNotEmpty) ...[
                      const SizedBox(height: 5),
                      Text(
                        doctor.specialties.join('، '),
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: Colors.white.withValues(alpha: 0.86),
                          height: 1.35,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              if (doctor.consultationFee != null)
                _HeroChip(
                  icon: Icons.payments_outlined,
                  label: '${l10n.get('fee')}: ${doctor.consultationFee} EGP',
                ),
              if (doctor.yearsOfExperience != null)
                _HeroChip(
                  icon: Icons.workspace_premium_outlined,
                  label: uxCopy(
                    context,
                    '${doctor.yearsOfExperience} سنة خبرة',
                    '${doctor.yearsOfExperience} years experience',
                  ),
                ),
              for (final branch in doctor.branches.take(2))
                _HeroChip(icon: Icons.location_on_outlined, label: branch),
              if (doctor.branches.isEmpty)
                _HeroChip(
                  icon: Icons.location_on_outlined,
                  label: uxCopy(context, 'المكان يضاف قريبًا', 'Location soon'),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class _HeroChip extends StatelessWidget {
  const _HeroChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.16),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white.withValues(alpha: 0.20)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 15, color: Colors.white),
          const SizedBox(width: 5),
          ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 220),
            child: Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.labelMedium?.copyWith(
                color: Colors.white,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
