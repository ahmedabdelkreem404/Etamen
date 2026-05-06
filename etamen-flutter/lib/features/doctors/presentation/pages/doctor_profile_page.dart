import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/presentation/providers/doctors_providers.dart';
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
              if (doctor.bio != null && doctor.bio!.trim().isNotEmpty)
                SoftMedicalCard(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        uxCopy(context, 'نبذة عن الدكتور', 'About the doctor'),
                        style: Theme.of(context).textTheme.titleMedium
                            ?.copyWith(fontWeight: FontWeight.w800),
                      ),
                      const SizedBox(height: 8),
                      Text(doctor.bio!, style: const TextStyle(height: 1.45)),
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
                  'التكلفة وحالة الموعد يتم تأكيدهم من السيرفر بعد الحجز.',
                  'Fee and appointment state are confirmed by the backend.',
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
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(22),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 72,
                height: 72,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.14),
                  borderRadius: BorderRadius.circular(22),
                ),
                child: const Icon(Icons.person, color: Colors.white, size: 38),
              ),
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
        color: Colors.white.withValues(alpha: 0.13),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 15, color: Colors.white),
          const SizedBox(width: 5),
          Text(
            label,
            style: Theme.of(context).textTheme.labelMedium?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}
