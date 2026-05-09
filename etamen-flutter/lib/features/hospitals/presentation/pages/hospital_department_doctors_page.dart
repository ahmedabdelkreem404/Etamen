import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:etamen_app/features/hospitals/presentation/providers/hospitals_providers.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class HospitalDepartmentDoctorsPage extends ConsumerWidget {
  const HospitalDepartmentDoctorsPage({
    required this.hospitalId,
    required this.departmentId,
    super.key,
  });

  final int hospitalId;
  final int departmentId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final params = HospitalDepartmentDoctorsParams(
      hospitalId: hospitalId,
      departmentId: departmentId,
    );
    final doctors = ref.watch(hospitalDepartmentDoctorsProvider(params));

    return AppScaffold(
      title: uxCopy(context, 'أطباء القسم', 'Department doctors'),
      body: doctors.when(
        loading: () => const LoadingView(),
        error: (error, _) => ErrorView(message: error.toString()),
        data: (result) => result.when(
          success: (items) => RefreshIndicator(
            onRefresh: () async {
              final refreshed = ref.refresh(
                hospitalDepartmentDoctorsProvider(params).future,
              );
              await refreshed;
            },
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(),
              padding: const EdgeInsets.all(16),
              children: [
                Text(
                  uxCopy(
                    context,
                    'اختر الطبيب ثم أكمل الحجز بنفس مسار المواعيد والدفع.',
                    'Choose a doctor, then continue with the existing booking and payment flow.',
                  ),
                  style: Theme.of(context).textTheme.bodyMedium,
                ),
                const SizedBox(height: 12),
                if (items.isEmpty)
                  EmptyView(
                    message: uxCopy(
                      context,
                      'لا يوجد أطباء متاحون في هذا القسم حاليًا.',
                      'No doctors are available in this department right now.',
                    ),
                    icon: Icons.person_search_outlined,
                  )
                else
                  for (final doctor in items)
                    DoctorCard(
                      doctor: doctor,
                      onTap: () =>
                          context.push(RouteNames.doctorProfile(doctor.id)),
                    ),
              ],
            ),
          ),
          failure: (failure) => ErrorView(message: failure.error.message),
        ),
      ),
    );
  }
}
