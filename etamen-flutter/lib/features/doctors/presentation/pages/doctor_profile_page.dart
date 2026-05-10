import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_button.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/appointments/domain/entities/hospital_booking_context.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/presentation/providers/doctors_providers.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/slot_picker.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class DoctorProfilePage extends ConsumerWidget {
  const DoctorProfilePage({
    required this.doctorId,
    this.hospitalContext,
    super.key,
  });

  final int doctorId;
  final HospitalBookingContext? hospitalContext;

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
              if (hospitalContext != null) ...[
                const SizedBox(height: 14),
                _HospitalContextCardFixed(contextHint: hospitalContext!),
              ],
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
                    : () => context.go(
                        RouteNames.doctorBooking(
                          doctor.id,
                          hospitalId: hospitalContext?.hospitalId,
                          departmentId: hospitalContext?.departmentId,
                          hospitalDoctorId: hospitalContext?.hospitalDoctorId,
                          hospitalName: hospitalContext?.hospitalName,
                          departmentName: hospitalContext?.departmentName,
                        ),
                      ),
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

// ignore: unused_element
class _HospitalContextCard extends StatelessWidget {
  const _HospitalContextCard({required this.contextHint});

  final HospitalBookingContext contextHint;

  String? get hospitalName => contextHint.hospitalName;

  String? get departmentName => contextHint.departmentName;

  @override
  Widget build(BuildContext context) {
    final label = _contextLabel(context);
    return SoftMedicalCard(
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: AppColors.medicalMint,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.apartment_outlined,
              color: AppColors.primaryDark,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  uxCopy(
                    context,
                    'الحجز من خلال مستشفى',
                    'Booking through a hospital',
                  ),
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    color: AppColors.primaryDark,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  label.isEmpty
                      ? uxCopy(
                          context,
                          'حجز من خلال مستشفى',
                          'Hospital booking',
                        )
                      : label,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.softText,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _contextLabel(BuildContext context) {
    return [
      hospitalName,
      departmentName == null
          ? null
          : uxCopy(
              context,
              'قسم $departmentName',
              '$departmentName department',
            ),
    ].whereType<String>().join(' - ');
  }
}

class _DoctorHero extends StatelessWidget {
  const _DoctorHero({required this.doctor});

  final Doctor doctor;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.softBorder),
        boxShadow: [
          BoxShadow(
            color: AppColors.primaryDark.withValues(alpha: 0.10),
            blurRadius: 18,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              DoctorAvatar(
                name: doctor.name,
                imageUrl: doctor.avatarUrl,
                size: 88,
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      doctor.name,
                      style: Theme.of(context).textTheme.titleLarge?.copyWith(
                        color: AppColors.text,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    if (doctor.specialties.isNotEmpty) ...[
                      const SizedBox(height: 5),
                      Text(
                        doctor.specialties.join('، '),
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: AppColors.muted,
                          height: 1.35,
                        ),
                      ),
                    ],
                    const SizedBox(height: 8),
                    _ProfileRatingRow(
                      ratingAverage: doctor.ratingAverage,
                      reviewsCount: doctor.reviewsCount,
                    ),
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

class _HospitalContextCardFixed extends StatelessWidget {
  const _HospitalContextCardFixed({required this.contextHint});

  final HospitalBookingContext contextHint;

  @override
  Widget build(BuildContext context) {
    final label = _contextLabel(context);
    return SoftMedicalCard(
      padding: const EdgeInsets.all(14),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: AppColors.medicalMint,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Icon(
              Icons.apartment_outlined,
              color: AppColors.primaryDark,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  uxCopy(
                    context,
                    '\u0627\u0644\u062D\u062C\u0632 \u0645\u0646 \u062E\u0644\u0627\u0644 \u0645\u0633\u062A\u0634\u0641\u0649',
                    'Booking through a hospital',
                  ),
                  style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    color: AppColors.primaryDark,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  label.isEmpty
                      ? uxCopy(
                          context,
                          '\u062D\u062C\u0632 \u0645\u0646 \u062E\u0644\u0627\u0644 \u0645\u0633\u062A\u0634\u0641\u0649',
                          'Hospital booking',
                        )
                      : label,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.softText,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  String _contextLabel(BuildContext context) {
    final hospitalName = contextHint.hospitalName;
    final departmentName = contextHint.departmentName;
    return [
      hospitalName,
      departmentName == null
          ? null
          : uxCopy(
              context,
              '\u0642\u0633\u0645 $departmentName',
              '$departmentName department',
            ),
    ].whereType<String>().join(' - ');
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
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: AppColors.border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 15, color: AppColors.primary),
          const SizedBox(width: 5),
          ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 220),
            child: Text(
              label,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(context).textTheme.labelMedium?.copyWith(
                color: AppColors.primaryDark,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _ProfileRatingRow extends StatelessWidget {
  const _ProfileRatingRow({
    required this.ratingAverage,
    required this.reviewsCount,
  });

  final double? ratingAverage;
  final int reviewsCount;

  @override
  Widget build(BuildContext context) {
    final hasRealRating = ratingAverage != null && reviewsCount > 0;
    final filledStars = hasRealRating ? ratingAverage!.round().clamp(0, 5) : 0;
    return Row(
      children: [
        for (var i = 0; i < 5; i++)
          Icon(
            hasRealRating && i < filledStars
                ? Icons.star_rounded
                : Icons.star_border_rounded,
            color: hasRealRating
                ? AppColors.medicalAccent
                : AppColors.muted.withValues(alpha: 0.55),
            size: 17,
          ),
        const SizedBox(width: 6),
        Flexible(
          child: Text(
            hasRealRating
                ? uxCopy(
                    context,
                    '${ratingAverage!.toStringAsFixed(1)} من 5 - $reviewsCount تقييم',
                    '${ratingAverage!.toStringAsFixed(1)} of 5 - $reviewsCount reviews',
                  )
                : uxCopy(
                    context,
                    'تقييمات المرضى قريبًا',
                    'Patient reviews soon',
                  ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: AppColors.muted,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
      ],
    );
  }
}
