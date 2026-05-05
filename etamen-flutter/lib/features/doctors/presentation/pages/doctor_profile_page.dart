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
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class DoctorProfilePage extends ConsumerWidget {
  const DoctorProfilePage({required this.doctorId, super.key});

  final int doctorId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final l10n = AppLocalizations.of(context);
    final details = ref.watch(doctorDetailsProvider(doctorId));
    final slots = ref.watch(doctorSlotsProvider(doctorId));

    return AppScaffold(
      title: l10n.get('doctors'),
      body: details.when(
        loading: () => const LoadingView(),
        error: (error, _) => ErrorView(message: error.toString()),
        data: (result) => result.when(
          success: (doctor) => ListView(
            padding: const EdgeInsets.all(16),
            children: [
              _DoctorHeader(doctor: doctor),
              const SizedBox(height: 16),
              if (doctor.bio != null && doctor.bio!.isNotEmpty)
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Text(doctor.bio!),
                  ),
                ),
              const SizedBox(height: 16),
              slots.when(
                loading: () => const LoadingView(),
                error: (error, _) => ErrorView(message: error.toString()),
                data: (slotResult) => slotResult.when(
                  success: (items) => SlotPicker(
                    slots: items.where((slot) => slot.isAvailable).toList(),
                    selectedSlot: null,
                    onSelected: (_) {},
                  ),
                  failure: (failure) =>
                      ErrorView(message: failure.error.message),
                ),
              ),
              const SizedBox(height: 24),
              AppButton(
                label: l10n.get('bookAppointment'),
                onPressed: doctor.doctorProfileId == null
                    ? null
                    : () => context.go(RouteNames.doctorBooking(doctor.id)),
              ),
            ],
          ),
          failure: (failure) => ErrorView(message: failure.error.message),
        ),
      ),
    );
  }
}

class _DoctorHeader extends StatelessWidget {
  const _DoctorHeader({required this.doctor});

  final Doctor doctor;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                CircleAvatar(
                  radius: 34,
                  backgroundColor: AppColors.primary.withValues(alpha: 0.12),
                  child: const Icon(
                    Icons.person,
                    color: AppColors.primary,
                    size: 34,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        doctor.name,
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      if (doctor.specialties.isNotEmpty)
                        Text(doctor.specialties.join('، ')),
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
                  Chip(
                    label: Text(
                      '${l10n.get('fee')}: ${doctor.consultationFee} EGP',
                    ),
                  ),
                if (doctor.yearsOfExperience != null)
                  Chip(label: Text('${doctor.yearsOfExperience} سنوات خبرة')),
                for (final branch in doctor.branches.take(2))
                  Chip(label: Text(branch)),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
