import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital_department.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';

class HospitalCard extends StatelessWidget {
  const HospitalCard({required this.hospital, required this.onTap, super.key});

  final Hospital hospital;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final location = hospital.locationLabel.isEmpty
        ? uxCopy(context, 'الموقع يضاف قريبًا', 'Location soon')
        : hospital.locationLabel;

    return SoftMedicalCard(
      onTap: onTap,
      margin: const EdgeInsets.only(bottom: 14),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const HospitalIconBlock(size: 74),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Expanded(
                          child: Text(
                            hospital.name,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: Theme.of(context).textTheme.titleMedium
                                ?.copyWith(fontWeight: FontWeight.w900),
                          ),
                        ),
                        const SizedBox(width: 8),
                        const _VerifiedBadge(),
                      ],
                    ),
                    const SizedBox(height: 7),
                    _InlineInfo(
                      icon: Icons.location_on_outlined,
                      label: location,
                    ),
                    if (hospital.primaryAddress != null) ...[
                      const SizedBox(height: 5),
                      Text(
                        hospital.primaryAddress!,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: Theme.of(context).textTheme.bodySmall?.copyWith(
                          color: AppColors.muted,
                          fontWeight: FontWeight.w600,
                        ),
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
              if (hospital.emergencyAvailable)
                HospitalBadge(
                  icon: Icons.emergency_outlined,
                  label: uxCopy(context, 'طوارئ', 'Emergency'),
                ),
              HospitalBadge(
                icon: Icons.account_tree_outlined,
                label: uxCopy(
                  context,
                  '${hospital.departmentsCount} أقسام',
                  '${hospital.departmentsCount} departments',
                ),
              ),
              HospitalBadge(
                icon: Icons.people_alt_outlined,
                label: uxCopy(
                  context,
                  '${hospital.doctorsCount} أطباء',
                  '${hospital.doctorsCount} doctors',
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Align(
            alignment: AlignmentDirectional.centerEnd,
            child: FilledButton.icon(
              onPressed: onTap,
              icon: const Icon(Icons.arrow_forward_rounded, size: 18),
              label: Text(uxCopy(context, 'عرض المستشفى', 'View hospital')),
            ),
          ),
        ],
      ),
    );
  }
}

class HospitalIconBlock extends StatelessWidget {
  const HospitalIconBlock({this.size = 70, super.key});

  final double size;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: AppColors.medicalMint,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.softBorder),
      ),
      child: Stack(
        alignment: Alignment.center,
        children: [
          Icon(
            Icons.local_hospital_outlined,
            color: AppColors.primary,
            size: size * 0.48,
          ),
          PositionedDirectional(
            top: 9,
            end: 9,
            child: Container(
              width: 11,
              height: 11,
              decoration: const BoxDecoration(
                color: AppColors.success,
                shape: BoxShape.circle,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class HospitalHero extends StatelessWidget {
  const HospitalHero({required this.hospital, super.key});

  final Hospital hospital;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          begin: AlignmentDirectional.topStart,
          end: AlignmentDirectional.bottomEnd,
          colors: [AppColors.primaryDark, AppColors.primary],
        ),
        borderRadius: BorderRadius.circular(22),
        boxShadow: [
          BoxShadow(
            color: AppColors.primaryDark.withValues(alpha: 0.16),
            blurRadius: 22,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(5),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                ),
                child: const HospitalIconBlock(size: 74),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(
                          Icons.verified_rounded,
                          color: Colors.white,
                          size: 18,
                        ),
                        const SizedBox(width: 5),
                        Text(
                          uxCopy(context, 'مستشفى معتمد', 'Approved hospital'),
                          style: Theme.of(context).textTheme.labelSmall
                              ?.copyWith(
                                color: Colors.white.withValues(alpha: 0.86),
                                fontWeight: FontWeight.w800,
                              ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Text(
                      hospital.name,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: Theme.of(context).textTheme.headlineSmall
                          ?.copyWith(
                            color: Colors.white,
                            fontWeight: FontWeight.w900,
                            height: 1.12,
                          ),
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
              if (hospital.emergencyAvailable)
                _HeroBadge(
                  icon: Icons.emergency_outlined,
                  label: uxCopy(context, 'طوارئ', 'Emergency'),
                ),
              if (hospital.hasOutpatient)
                _HeroBadge(
                  icon: Icons.meeting_room_outlined,
                  label: uxCopy(context, 'عيادات خارجية', 'Outpatient'),
                ),
              if (hospital.hasInpatient)
                _HeroBadge(
                  icon: Icons.hotel_outlined,
                  label: uxCopy(context, 'إقامة داخلية', 'Inpatient'),
                ),
              if (hospital.hasIcu)
                _HeroBadge(
                  icon: Icons.monitor_heart_outlined,
                  label: uxCopy(context, 'عناية مركزة', 'ICU'),
                ),
              if (hospital.hasAmbulance)
                _HeroBadge(
                  icon: Icons.local_shipping_outlined,
                  label: uxCopy(context, 'إسعاف', 'Ambulance'),
                ),
            ],
          ),
        ],
      ),
    );
  }
}

class HospitalBadge extends StatelessWidget {
  const HospitalBadge({required this.icon, required this.label, super.key});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 6),
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
          Text(
            label,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: AppColors.primaryDark,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }
}

class HospitalDepartmentCard extends StatelessWidget {
  const HospitalDepartmentCard({
    required this.department,
    required this.onTap,
    super.key,
  });

  final HospitalDepartment department;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return SoftMedicalCard(
      onTap: onTap,
      margin: const EdgeInsets.only(bottom: 10),
      padding: const EdgeInsets.all(13),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: AppColors.medicalMint,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: AppColors.border),
            ),
            child: const Icon(
              Icons.account_tree_outlined,
              color: AppColors.primary,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  department.name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: Theme.of(
                    context,
                  ).textTheme.titleSmall?.copyWith(fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 4),
                Text(
                  uxCopy(
                    context,
                    '${department.doctorsCount} أطباء متاحين',
                    '${department.doctorsCount} available doctors',
                  ),
                  style: Theme.of(context).textTheme.bodySmall?.copyWith(
                    color: AppColors.muted,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
          const Icon(Icons.chevron_right_rounded, color: AppColors.primary),
        ],
      ),
    );
  }
}

class LocationSummaryCard extends StatelessWidget {
  const LocationSummaryCard({
    required this.hospital,
    this.onOpenMap,
    super.key,
  });

  final Hospital hospital;
  final VoidCallback? onOpenMap;

  @override
  Widget build(BuildContext context) {
    final address =
        hospital.primaryAddress ??
        uxCopy(context, 'العنوان يضاف قريبًا', 'Address soon');
    final coordinates = hospital.latitude != null && hospital.longitude != null
        ? '${hospital.latitude!.toStringAsFixed(4)}, ${hospital.longitude!.toStringAsFixed(4)}'
        : uxCopy(
            context,
            'إحداثيات الخريطة غير متاحة',
            'Map coordinates unavailable',
          );

    return SoftMedicalCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          HomeSectionHeader(
            title: uxCopy(context, 'الموقع', 'Location'),
            subtitle: hospital.locationLabel.isEmpty
                ? null
                : hospital.locationLabel,
          ),
          const SizedBox(height: 10),
          _InlineInfo(icon: Icons.place_outlined, label: address),
          const SizedBox(height: 8),
          _InlineInfo(icon: Icons.map_outlined, label: coordinates),
          const SizedBox(height: 12),
          Align(
            alignment: AlignmentDirectional.centerEnd,
            child: OutlinedButton.icon(
              onPressed: onOpenMap,
              icon: const Icon(Icons.open_in_new_rounded, size: 18),
              label: Text(uxCopy(context, 'افتح على الخريطة', 'Open map')),
            ),
          ),
        ],
      ),
    );
  }
}

class _InlineInfo extends StatelessWidget {
  const _InlineInfo({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 17, color: AppColors.primary),
        const SizedBox(width: 6),
        Expanded(
          child: Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: Theme.of(context).textTheme.bodySmall?.copyWith(
              color: AppColors.softText,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ],
    );
  }
}

class _VerifiedBadge extends StatelessWidget {
  const _VerifiedBadge();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.success.withValues(alpha: 0.10),
        borderRadius: BorderRadius.circular(999),
      ),
      child: const Icon(
        Icons.verified_rounded,
        color: AppColors.success,
        size: 16,
      ),
    );
  }
}

class _HeroBadge extends StatelessWidget {
  const _HeroBadge({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 7),
      decoration: BoxDecoration(
        color: Colors.white.withValues(alpha: 0.18),
        borderRadius: BorderRadius.circular(999),
        border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: Colors.white, size: 15),
          const SizedBox(width: 5),
          Text(
            label,
            style: Theme.of(context).textTheme.labelSmall?.copyWith(
              color: Colors.white,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }
}
