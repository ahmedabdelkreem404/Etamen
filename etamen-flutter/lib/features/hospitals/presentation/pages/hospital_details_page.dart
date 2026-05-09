import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/widgets/app_scaffold.dart';
import 'package:etamen_app/core/widgets/empty_view.dart';
import 'package:etamen_app/core/widgets/error_view.dart';
import 'package:etamen_app/core/widgets/loading_view.dart';
import 'package:etamen_app/features/doctors/presentation/widgets/doctor_card.dart';
import 'package:etamen_app/features/hospitals/domain/entities/hospital.dart';
import 'package:etamen_app/features/hospitals/presentation/providers/hospitals_providers.dart';
import 'package:etamen_app/features/hospitals/presentation/widgets/hospital_widgets.dart';
import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

class HospitalDetailsPage extends ConsumerWidget {
  const HospitalDetailsPage({required this.hospitalId, super.key});

  final int hospitalId;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final details = ref.watch(hospitalDetailsProvider(hospitalId));
    final departments = ref.watch(hospitalDepartmentsProvider(hospitalId));
    final doctors = ref.watch(hospitalDoctorsProvider(hospitalId));

    return AppScaffold(
      title: uxCopy(context, 'تفاصيل المستشفى', 'Hospital details'),
      body: details.when(
        loading: () => const LoadingView(),
        error: (error, _) => ErrorView(message: error.toString()),
        data: (result) => result.when(
          success: (hospital) => ListView(
            padding: const EdgeInsets.all(16),
            children: [
              HospitalHero(hospital: hospital),
              if (hospital.description != null) ...[
                const SizedBox(height: 14),
                SoftMedicalCard(
                  child: Text(
                    hospital.description!,
                    style: Theme.of(
                      context,
                    ).textTheme.bodyMedium?.copyWith(height: 1.45),
                  ),
                ),
              ],
              const SizedBox(height: 14),
              LocationSummaryCard(
                hospital: hospital,
                onOpenMap: _canOpenMap(hospital)
                    ? () => _openMap(hospital)
                    : null,
              ),
              const SizedBox(height: 18),
              HomeSectionHeader(
                title: uxCopy(context, 'الأقسام', 'Departments'),
                subtitle: uxCopy(
                  context,
                  'اختار القسم ثم افتح طبيب للحجز بنفس مسار الدفع الحالي.',
                  'Choose a department, then book through the existing flow.',
                ),
              ),
              const SizedBox(height: 10),
              departments.when(
                loading: () => const LoadingView(),
                error: (error, _) => ErrorView(message: error.toString()),
                data: (departmentResult) => departmentResult.when(
                  success: (items) => items.isEmpty
                      ? EmptyView(
                          message: uxCopy(
                            context,
                            'لا توجد أقسام متاحة حاليًا.',
                            'No departments are available right now.',
                          ),
                          icon: Icons.account_tree_outlined,
                        )
                      : Column(
                          children: [
                            for (final department in items)
                              HospitalDepartmentCard(
                                department: department,
                                onTap: () => context.push(
                                  RouteNames.hospitalDepartmentDoctors(
                                    hospital.id,
                                    department.id,
                                  ),
                                ),
                              ),
                          ],
                        ),
                  failure: (failure) =>
                      ErrorView(message: failure.error.message),
                ),
              ),
              const SizedBox(height: 18),
              HomeSectionHeader(
                title: uxCopy(context, 'أطباء المستشفى', 'Hospital doctors'),
                subtitle: uxCopy(
                  context,
                  'الحجز يتم من صفحة الطبيب ولا يتم تغيير سعر أو حالة الدفع من الواجهة.',
                  'Booking stays on the doctor flow; price and payment state stay backend-owned.',
                ),
              ),
              const SizedBox(height: 10),
              doctors.when(
                loading: () => const LoadingView(),
                error: (error, _) => ErrorView(message: error.toString()),
                data: (doctorResult) => doctorResult.when(
                  success: (items) => items.isEmpty
                      ? EmptyView(
                          message: uxCopy(
                            context,
                            'لا يوجد أطباء مرتبطون بالمستشفى حاليًا.',
                            'No linked doctors yet.',
                          ),
                          icon: Icons.person_search_outlined,
                        )
                      : Column(
                          children: [
                            for (final doctor in items.take(3))
                              DoctorCard(
                                doctor: doctor,
                                onTap: () => context.push(
                                  RouteNames.doctorProfile(doctor.id),
                                ),
                              ),
                          ],
                        ),
                  failure: (failure) =>
                      ErrorView(message: failure.error.message),
                ),
              ),
            ],
          ),
          failure: (failure) => ErrorView(message: failure.error.message),
        ),
      ),
    );
  }

  bool _canOpenMap(Hospital hospital) {
    return hospital.latitude != null && hospital.longitude != null;
  }

  Future<void> _openMap(Hospital hospital) async {
    final uri = Uri.parse(
      'https://www.google.com/maps/search/?api=1&query=${hospital.latitude},${hospital.longitude}',
    );
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }
}
