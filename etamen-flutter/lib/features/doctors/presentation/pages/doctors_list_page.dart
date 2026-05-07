import 'package:etamen_app/app/localization/app_localizations.dart';
import 'package:etamen_app/app/theme/app_colors.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/doctors/domain/entities/doctor.dart';
import 'package:etamen_app/features/doctors/presentation/providers/doctors_providers.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class DoctorsListPage extends ConsumerStatefulWidget {
  const DoctorsListPage({this.showAppBar = true, super.key});

  final bool showAppBar;

  @override
  ConsumerState<DoctorsListPage> createState() => _DoctorsListPageState();
}

class _DoctorsListPageState extends ConsumerState<DoctorsListPage> {
  String _query = '';
  String? _specialty;

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);
    final state = ref.watch(doctorsListControllerProvider);
    final content = RefreshIndicator(
      onRefresh: () => ref.read(doctorsListControllerProvider.notifier).load(),
      child: Builder(
        builder: (context) {
          if (state.isLoading) return const LoadingView();
          if (state.error != null) {
            return ErrorView(
              message: state.error!.message,
              onRetry: () =>
                  ref.read(doctorsListControllerProvider.notifier).load(),
            );
          }
          if (state.isEmpty) {
            return ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              children: [
                const _DoctorsListHero(),
                const SizedBox(height: 14),
                EmptyView(
                  message: l10n.get('emptyDoctors'),
                  icon: Icons.medical_services_outlined,
                ),
              ],
            );
          }

          final specialties = _specialtiesFrom(state.doctors);
          final doctors = _filteredDoctors(state.doctors);

          return ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.all(16),
            children: [
              if (!widget.showAppBar) ...[
                Text(
                  l10n.get('doctors'),
                  style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  uxCopy(
                    context,
                    'اختار الدكتور المناسب واحجز الموعد من غير تعقيد.',
                    'Find the right doctor and book with less friction.',
                  ),
                  style: Theme.of(
                    context,
                  ).textTheme.bodyMedium?.copyWith(color: Colors.grey[700]),
                ),
                const SizedBox(height: 16),
              ],
              const _DoctorsListHero(),
              const SizedBox(height: 14),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(22),
                  boxShadow: [
                    BoxShadow(
                      color: AppColors.primaryDark.withValues(alpha: 0.08),
                      blurRadius: 20,
                      offset: const Offset(0, 10),
                    ),
                  ],
                ),
                child: TextField(
                  onChanged: (value) => setState(() => _query = value),
                  decoration: InputDecoration(
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: const Icon(Icons.tune_outlined),
                    hintText: uxCopy(
                      context,
                      'ابحث باسم الدكتور أو التخصص أو المنطقة',
                      'Search by doctor, specialty, or area',
                    ),
                  ),
                ),
              ),
              if (specialties.isNotEmpty) ...[
                const SizedBox(height: 12),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  alignment: WrapAlignment.end,
                  children: [
                    ChoiceChip(
                      label: Text(l10n.get('all')),
                      selected: _specialty == null,
                      onSelected: (_) => setState(() => _specialty = null),
                    ),
                    for (final value in specialties)
                      ChoiceChip(
                        label: Text(value, overflow: TextOverflow.ellipsis),
                        selected: _specialty == value,
                        onSelected: (_) => setState(() => _specialty = value),
                      ),
                  ],
                ),
              ],
              const SizedBox(height: 16),
              if (doctors.isEmpty)
                EmptyView(
                  message: uxCopy(
                    context,
                    'لا توجد نتائج مطابقة للبحث الحالي. جرّب اسمًا أو تخصصًا مختلفًا.',
                    'No doctors match the current filters. Try another name or specialty.',
                  ),
                  icon: Icons.search_off_outlined,
                )
              else
                for (final doctor in doctors)
                  DoctorCard(
                    doctor: doctor,
                    onTap: () =>
                        context.push(RouteNames.doctorProfile(doctor.id)),
                  ),
            ],
          );
        },
      ),
    );

    if (!widget.showAppBar) return content;

    return AppScaffold(title: l10n.get('doctors'), body: content);
  }

  List<String> _specialtiesFrom(List<Doctor> doctors) {
    final values = <String>{};
    for (final doctor in doctors) {
      values.addAll(doctor.specialties.where((item) => item.trim().isNotEmpty));
    }
    return values.take(12).toList();
  }

  List<Doctor> _filteredDoctors(List<Doctor> doctors) {
    final query = _query.trim().toLowerCase();
    return doctors.where((doctor) {
      final specialtyMatch =
          _specialty == null || doctor.specialties.contains(_specialty);
      final haystack = [
        doctor.name,
        ...doctor.specialties,
        ...doctor.branches,
      ].join(' ').toLowerCase();
      final queryMatch = query.isEmpty || haystack.contains(query);
      return specialtyMatch && queryMatch;
    }).toList();
  }
}

class _DoctorsListHero extends StatelessWidget {
  const _DoctorsListHero();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.primary,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withValues(alpha: 0.18),
            blurRadius: 22,
            offset: const Offset(0, 12),
          ),
        ],
      ),
      child: Stack(
        children: [
          PositionedDirectional(
            end: -22,
            top: -20,
            child: Transform.rotate(
              angle: -0.32,
              child: Container(
                width: 110,
                height: 150,
                color: Colors.white.withValues(alpha: 0.10),
              ),
            ),
          ),
          Row(
            children: [
              Container(
                width: 62,
                height: 72,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: const Icon(
                  Icons.person_search_outlined,
                  color: AppColors.primary,
                  size: 34,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      uxCopy(context, 'ابحث عن طبيب', 'Find A Doctor'),
                      style: Theme.of(context).textTheme.titleMedium?.copyWith(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      uxCopy(
                        context,
                        'بطاقات الأطباء تعرض التخصص والمكان والسعر والحجز بوضوح.',
                        'Doctor cards show specialty, location, fee, and booking clearly.',
                      ),
                      style: Theme.of(context).textTheme.bodySmall?.copyWith(
                        color: Colors.white.withValues(alpha: 0.88),
                        height: 1.35,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
