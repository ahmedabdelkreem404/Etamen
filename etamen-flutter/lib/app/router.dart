import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/core/legal/legal_document_type.dart';
import 'package:etamen_app/features/account/presentation/pages/about_page.dart';
import 'package:etamen_app/features/account/presentation/pages/account_page.dart';
import 'package:etamen_app/features/account/presentation/pages/language_settings_page.dart';
import 'package:etamen_app/features/account/presentation/pages/legal_page.dart';
import 'package:etamen_app/features/account/presentation/pages/settings_page.dart';
import 'package:etamen_app/features/account/presentation/pages/support_page.dart';
import 'package:etamen_app/features/appointments/domain/entities/hospital_booking_context.dart';
import 'package:etamen_app/features/appointments/presentation/pages/appointment_booking_page.dart';
import 'package:etamen_app/features/appointments/presentation/pages/appointment_details_page.dart';
import 'package:etamen_app/features/appointments/presentation/pages/appointment_result_page.dart';
import 'package:etamen_app/features/appointments/presentation/pages/my_appointments_page.dart';
import 'package:etamen_app/features/ai_assistant/presentation/pages/ai_chat_page.dart';
import 'package:etamen_app/features/ai_assistant/presentation/pages/ai_context_preview_page.dart';
import 'package:etamen_app/features/ai_assistant/presentation/pages/ai_conversations_page.dart';
import 'package:etamen_app/features/auth/presentation/pages/login_page.dart';
import 'package:etamen_app/features/auth/presentation/pages/register_page.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:etamen_app/features/care_plans/presentation/pages/care_plan_checkin_page.dart';
import 'package:etamen_app/features/care_plans/presentation/pages/care_plan_day_page.dart';
import 'package:etamen_app/features/care_plans/presentation/pages/care_plan_details_page.dart';
import 'package:etamen_app/features/care_plans/presentation/pages/care_plan_progress_page.dart';
import 'package:etamen_app/features/care_plans/presentation/pages/care_plans_page.dart';
import 'package:etamen_app/features/care_plans/presentation/pages/meal_log_page.dart';
import 'package:etamen_app/features/doctors/presentation/pages/doctor_profile_page.dart';
import 'package:etamen_app/features/doctors/presentation/pages/doctors_list_page.dart';
import 'package:etamen_app/features/fitness/presentation/pages/coach_booking_details_page.dart';
import 'package:etamen_app/features/fitness/presentation/pages/coach_details_page.dart';
import 'package:etamen_app/features/fitness/presentation/pages/coaches_page.dart';
import 'package:etamen_app/features/fitness/presentation/pages/gym_booking_details_page.dart';
import 'package:etamen_app/features/fitness/presentation/pages/gym_details_page.dart';
import 'package:etamen_app/features/fitness/presentation/pages/gyms_page.dart';
import 'package:etamen_app/features/fitness/presentation/pages/my_coach_bookings_page.dart';
import 'package:etamen_app/features/fitness/presentation/pages/my_gym_bookings_page.dart';
import 'package:etamen_app/features/home/presentation/pages/home_page.dart';
import 'package:etamen_app/features/hospitals/presentation/pages/hospital_department_doctors_page.dart';
import 'package:etamen_app/features/hospitals/presentation/pages/hospital_details_page.dart';
import 'package:etamen_app/features/hospitals/presentation/pages/hospitals_page.dart';
import 'package:etamen_app/features/health/domain/entities/vital_record.dart';
import 'package:etamen_app/features/health/presentation/pages/add_vital_page.dart';
import 'package:etamen_app/features/health/presentation/pages/edit_health_profile_page.dart';
import 'package:etamen_app/features/health/presentation/pages/health_dashboard_page.dart';
import 'package:etamen_app/features/health/presentation/pages/health_profile_page.dart';
import 'package:etamen_app/features/health/presentation/pages/vitals_list_page.dart';
import 'package:etamen_app/features/labs/presentation/pages/lab_cart_page.dart';
import 'package:etamen_app/features/labs/presentation/pages/lab_order_details_page.dart';
import 'package:etamen_app/features/labs/presentation/pages/lab_tests_page.dart';
import 'package:etamen_app/features/labs/presentation/pages/labs_page.dart';
import 'package:etamen_app/features/labs/presentation/pages/my_lab_orders_page.dart';
import 'package:etamen_app/features/medications/presentation/pages/create_medication_reminder_page.dart';
import 'package:etamen_app/features/medications/presentation/pages/medication_adherence_page.dart';
import 'package:etamen_app/features/medications/presentation/pages/medication_reminder_details_page.dart';
import 'package:etamen_app/features/medications/presentation/pages/medication_reminders_page.dart';
import 'package:etamen_app/features/medications/presentation/pages/medications_dashboard_page.dart';
import 'package:etamen_app/features/medications/presentation/pages/today_medications_page.dart';
import 'package:etamen_app/features/notifications/presentation/pages/notification_details_page.dart';
import 'package:etamen_app/features/notifications/presentation/pages/notification_preferences_page.dart';
import 'package:etamen_app/features/notifications/presentation/pages/notifications_page.dart';
import 'package:etamen_app/features/payments/presentation/pages/manual_payment_page.dart';
import 'package:etamen_app/features/payments/presentation/pages/payment_page.dart';
import 'package:etamen_app/features/payments/presentation/pages/payment_status_page.dart';
import 'package:etamen_app/features/payments/presentation/pages/paymob_checkout_page.dart';
import 'package:etamen_app/features/pharmacy/presentation/pages/my_pharmacy_orders_page.dart';
import 'package:etamen_app/features/pharmacy/presentation/pages/pharmacies_page.dart';
import 'package:etamen_app/features/pharmacy/presentation/pages/pharmacy_cart_page.dart';
import 'package:etamen_app/features/pharmacy/presentation/pages/pharmacy_order_details_page.dart';
import 'package:etamen_app/features/pharmacy/presentation/pages/pharmacy_products_page.dart';
import 'package:etamen_app/features/pharmacy/presentation/pages/prescription_upload_page.dart';
import 'package:etamen_app/features/radiology/presentation/pages/my_radiology_orders_page.dart';
import 'package:etamen_app/features/radiology/presentation/pages/radiology_home_page.dart';
import 'package:etamen_app/features/radiology/presentation/pages/radiology_order_builder_page.dart';
import 'package:etamen_app/features/radiology/presentation/pages/radiology_order_details_page.dart';
import 'package:etamen_app/features/splash/presentation/pages/splash_page.dart';
import 'package:etamen_app/features/workspaces/presentation/pages/platform_admin_dashboard_page.dart';
import 'package:etamen_app/features/workspaces/presentation/pages/provider_dashboard_page.dart';
import 'package:etamen_app/features/workspaces/presentation/providers/workspace_providers.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final refreshNotifier = RouterRefreshNotifier(ref);

  ref.onDispose(refreshNotifier.dispose);

  return GoRouter(
    initialLocation: RouteNames.splash,
    refreshListenable: refreshNotifier,
    redirect: (context, state) {
      final authState = ref.read(authControllerProvider);
      final location = state.matchedLocation;
      final isAuthRoute =
          location == RouteNames.login || location == RouteNames.register;

      if (authState.status == AuthStatus.unknown) {
        return location == RouteNames.splash ? null : RouteNames.splash;
      }

      if (authState.status == AuthStatus.unauthenticated) {
        return isAuthRoute ? null : RouteNames.login;
      }

      if (authState.status == AuthStatus.authenticated &&
          (isAuthRoute || location == RouteNames.splash)) {
        final workspace = ref
            .read(workspaceControllerProvider)
            .selectedWorkspace;
        if (workspace?.isProvider == true && workspace?.providerId != null) {
          return RouteNames.providerDashboard(workspace!.providerId!);
        }
        if (workspace?.isPlatformAdmin == true) {
          return RouteNames.platformAdminDashboard;
        }
        return RouteNames.home;
      }

      return null;
    },
    routes: [
      GoRoute(
        path: RouteNames.splash,
        builder: (context, state) => const SplashPage(),
      ),
      GoRoute(
        path: RouteNames.login,
        builder: (context, state) => const LoginPage(),
      ),
      GoRoute(
        path: RouteNames.register,
        builder: (context, state) => const RegisterPage(),
      ),
      GoRoute(
        path: RouteNames.home,
        builder: (context, state) => const HomePage(),
      ),
      GoRoute(
        path: '/workspace/provider/:providerId',
        builder: (context, state) {
          final providerId = int.tryParse(
            state.pathParameters['providerId'] ?? '',
          );
          return ProviderDashboardPage(providerId: providerId ?? 0);
        },
      ),
      GoRoute(
        path: RouteNames.platformAdminDashboard,
        builder: (context, state) => const PlatformAdminDashboardPage(),
      ),
      GoRoute(
        path: RouteNames.doctors,
        builder: (context, state) => const DoctorsListPage(),
      ),
      GoRoute(
        path: RouteNames.appointments,
        builder: (context, state) => const MyAppointmentsPage(),
      ),
      GoRoute(
        path: RouteNames.pharmacies,
        builder: (context, state) => const PharmaciesPage(),
      ),
      GoRoute(
        path: RouteNames.labs,
        builder: (context, state) => const LabsPage(),
      ),
      GoRoute(
        path: RouteNames.radiology,
        builder: (context, state) => const RadiologyHomePage(),
      ),
      GoRoute(
        path: RouteNames.gyms,
        builder: (context, state) => const GymsPage(),
      ),
      GoRoute(
        path: RouteNames.gymBookings,
        builder: (context, state) => const MyGymBookingsPage(),
      ),
      GoRoute(
        path: RouteNames.coaches,
        builder: (context, state) => const CoachesPage(),
      ),
      GoRoute(
        path: RouteNames.coachBookings,
        builder: (context, state) => const MyCoachBookingsPage(),
      ),
      GoRoute(
        path: RouteNames.hospitals,
        builder: (context, state) => const HospitalsPage(),
      ),
      GoRoute(
        path: RouteNames.health,
        builder: (context, state) => const HealthDashboardPage(),
      ),
      GoRoute(
        path: RouteNames.medications,
        builder: (context, state) => const MedicationsDashboardPage(),
      ),
      GoRoute(
        path: RouteNames.carePlans,
        builder: (context, state) => const CarePlansPage(),
      ),
      GoRoute(
        path: RouteNames.ai,
        builder: (context, state) => const AiConversationsPage(),
      ),
      GoRoute(
        path: '/ai/conversations/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return AiChatPage(conversationId: id ?? 0);
        },
      ),
      GoRoute(
        path: RouteNames.aiContextPreview,
        builder: (context, state) => const AiContextPreviewPage(),
      ),
      GoRoute(
        path: RouteNames.notifications,
        builder: (context, state) => const NotificationsPage(),
      ),
      GoRoute(
        path: RouteNames.notificationPreferences,
        builder: (context, state) => const NotificationPreferencesPage(),
      ),
      GoRoute(
        path: '/notifications/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return NotificationDetailsPage(notificationId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/care-plans/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return CarePlanDetailsPage(planId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/care-plans/:id/day/:dayId',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          final dayId = int.tryParse(state.pathParameters['dayId'] ?? '');
          return CarePlanDayPage(planId: id ?? 0, dayId: dayId ?? 0);
        },
      ),
      GoRoute(
        path: '/care-plans/:id/checkin',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return CarePlanCheckinPage(planId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/care-plans/:id/meal-log',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return MealLogPage(planId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/care-plans/:id/progress',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return CarePlanProgressPage(planId: id ?? 0);
        },
      ),
      GoRoute(
        path: RouteNames.medicationReminders,
        builder: (context, state) => const MedicationRemindersPage(),
      ),
      GoRoute(
        path: RouteNames.createMedicationReminder,
        builder: (context, state) => const CreateMedicationReminderPage(),
      ),
      GoRoute(
        path: RouteNames.todayMedications,
        builder: (context, state) => const TodayMedicationsPage(),
      ),
      GoRoute(
        path: RouteNames.medicationAdherence,
        builder: (context, state) => const MedicationAdherencePage(),
      ),
      GoRoute(
        path: '/medications/reminders/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return MedicationReminderDetailsPage(reminderId: id ?? 0);
        },
      ),
      GoRoute(
        path: RouteNames.healthProfile,
        builder: (context, state) => const HealthProfilePage(),
      ),
      GoRoute(
        path: RouteNames.editHealthProfile,
        builder: (context, state) => const EditHealthProfilePage(),
      ),
      GoRoute(
        path: RouteNames.healthVitals,
        builder: (context, state) => const VitalsListPage(),
      ),
      GoRoute(
        path: '/health/vitals/add',
        builder: (context, state) => const AddVitalPage(),
      ),
      GoRoute(
        path: '/health/vitals/add/:type',
        builder: (context, state) {
          final type = VitalType.fromWire(state.pathParameters['type']);
          return AddVitalPage(
            initialType: type == VitalType.unknown
                ? VitalType.bloodPressure
                : type,
          );
        },
      ),
      GoRoute(
        path: '/labs/:id/tests',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return LabTestsPage(labId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/hospitals/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return HospitalDetailsPage(hospitalId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/hospitals/:hospitalId/departments/:departmentId/doctors',
        builder: (context, state) {
          final hospitalId = int.tryParse(
            state.pathParameters['hospitalId'] ?? '',
          );
          final departmentId = int.tryParse(
            state.pathParameters['departmentId'] ?? '',
          );
          return HospitalDepartmentDoctorsPage(
            hospitalId: hospitalId ?? 0,
            departmentId: departmentId ?? 0,
          );
        },
      ),
      GoRoute(
        path: RouteNames.labCart,
        builder: (context, state) => const LabCartPage(),
      ),
      GoRoute(
        path: RouteNames.labOrders,
        builder: (context, state) => const MyLabOrdersPage(),
      ),
      GoRoute(
        path: RouteNames.radiologyOrderBuilder,
        builder: (context, state) => const RadiologyOrderBuilderPage(),
      ),
      GoRoute(
        path: RouteNames.radiologyOrders,
        builder: (context, state) => const MyRadiologyOrdersPage(),
      ),
      GoRoute(
        path: '/radiology/orders/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return RadiologyOrderDetailsPage(orderId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/gyms/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return GymDetailsPage(gymId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/gym/bookings/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return GymBookingDetailsPage(bookingId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/coaches/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return CoachDetailsPage(coachId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/coach/bookings/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return CoachBookingDetailsPage(bookingId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/lab-orders/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return LabOrderDetailsPage(orderId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/pharmacies/:id/products',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return PharmacyProductsPage(pharmacyId: id ?? 0);
        },
      ),
      GoRoute(
        path: RouteNames.pharmacyCart,
        builder: (context, state) => const PharmacyCartPage(),
      ),
      GoRoute(
        path: RouteNames.pharmacyPrescriptionUpload,
        builder: (context, state) => const PrescriptionUploadPage(),
      ),
      GoRoute(
        path: RouteNames.pharmacyOrders,
        builder: (context, state) => const MyPharmacyOrdersPage(),
      ),
      GoRoute(
        path: '/pharmacy/orders/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return PharmacyOrderDetailsPage(orderId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/doctors/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return DoctorProfilePage(
            doctorId: id ?? 0,
            hospitalContext: HospitalBookingContext.fromQueryParameters(
              state.uri.queryParameters,
            ),
          );
        },
      ),
      GoRoute(
        path: '/doctors/:id/booking',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return AppointmentBookingPage(
            doctorId: id ?? 0,
            hospitalContext: HospitalBookingContext.fromQueryParameters(
              state.uri.queryParameters,
            ),
          );
        },
      ),
      GoRoute(
        path: '/appointments/:id/result',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return AppointmentResultPage(appointmentId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/appointments/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return AppointmentDetailsPage(appointmentId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/payments/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          final appointmentId = int.tryParse(
            state.uri.queryParameters['appointmentId'] ?? '',
          );
          final pharmacyOrderId = int.tryParse(
            state.uri.queryParameters['pharmacyOrderId'] ?? '',
          );
          final labOrderId = int.tryParse(
            state.uri.queryParameters['labOrderId'] ?? '',
          );
          final radiologyOrderId = int.tryParse(
            state.uri.queryParameters['radiologyOrderId'] ?? '',
          );
          final gymBookingId = int.tryParse(
            state.uri.queryParameters['gymBookingId'] ?? '',
          );
          final coachBookingId = int.tryParse(
            state.uri.queryParameters['coachBookingId'] ?? '',
          );
          return PaymentPage(
            paymentId: id ?? 0,
            appointmentId: appointmentId,
            pharmacyOrderId: pharmacyOrderId,
            labOrderId: labOrderId,
            radiologyOrderId: radiologyOrderId,
            gymBookingId: gymBookingId,
            coachBookingId: coachBookingId,
          );
        },
      ),
      GoRoute(
        path: '/payments/:id/manual',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          final methodId = int.tryParse(
            state.uri.queryParameters['methodId'] ?? '',
          );
          final appointmentId = int.tryParse(
            state.uri.queryParameters['appointmentId'] ?? '',
          );
          final pharmacyOrderId = int.tryParse(
            state.uri.queryParameters['pharmacyOrderId'] ?? '',
          );
          final labOrderId = int.tryParse(
            state.uri.queryParameters['labOrderId'] ?? '',
          );
          final radiologyOrderId = int.tryParse(
            state.uri.queryParameters['radiologyOrderId'] ?? '',
          );
          final gymBookingId = int.tryParse(
            state.uri.queryParameters['gymBookingId'] ?? '',
          );
          final coachBookingId = int.tryParse(
            state.uri.queryParameters['coachBookingId'] ?? '',
          );
          return ManualPaymentPage(
            paymentId: id ?? 0,
            methodId: methodId ?? 0,
            appointmentId: appointmentId,
            pharmacyOrderId: pharmacyOrderId,
            labOrderId: labOrderId,
            radiologyOrderId: radiologyOrderId,
            gymBookingId: gymBookingId,
            coachBookingId: coachBookingId,
          );
        },
      ),
      GoRoute(
        path: '/payments/:id/status',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          final appointmentId = int.tryParse(
            state.uri.queryParameters['appointmentId'] ?? '',
          );
          final pharmacyOrderId = int.tryParse(
            state.uri.queryParameters['pharmacyOrderId'] ?? '',
          );
          final labOrderId = int.tryParse(
            state.uri.queryParameters['labOrderId'] ?? '',
          );
          final radiologyOrderId = int.tryParse(
            state.uri.queryParameters['radiologyOrderId'] ?? '',
          );
          final gymBookingId = int.tryParse(
            state.uri.queryParameters['gymBookingId'] ?? '',
          );
          final coachBookingId = int.tryParse(
            state.uri.queryParameters['coachBookingId'] ?? '',
          );
          return PaymentStatusPage(
            paymentId: id ?? 0,
            appointmentId: appointmentId,
            pharmacyOrderId: pharmacyOrderId,
            labOrderId: labOrderId,
            radiologyOrderId: radiologyOrderId,
            gymBookingId: gymBookingId,
            coachBookingId: coachBookingId,
          );
        },
      ),
      GoRoute(
        path: '/payments/:id/paymob',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          final appointmentId = int.tryParse(
            state.uri.queryParameters['appointmentId'] ?? '',
          );
          final pharmacyOrderId = int.tryParse(
            state.uri.queryParameters['pharmacyOrderId'] ?? '',
          );
          final labOrderId = int.tryParse(
            state.uri.queryParameters['labOrderId'] ?? '',
          );
          final radiologyOrderId = int.tryParse(
            state.uri.queryParameters['radiologyOrderId'] ?? '',
          );
          final gymBookingId = int.tryParse(
            state.uri.queryParameters['gymBookingId'] ?? '',
          );
          final coachBookingId = int.tryParse(
            state.uri.queryParameters['coachBookingId'] ?? '',
          );
          return PaymobCheckoutPage(
            paymentId: id ?? 0,
            appointmentId: appointmentId,
            pharmacyOrderId: pharmacyOrderId,
            labOrderId: labOrderId,
            radiologyOrderId: radiologyOrderId,
            gymBookingId: gymBookingId,
            coachBookingId: coachBookingId,
          );
        },
      ),
      GoRoute(
        path: RouteNames.account,
        builder: (context, state) => const AccountPage(),
      ),
      GoRoute(
        path: RouteNames.accountSettings,
        builder: (context, state) => const SettingsPage(),
      ),
      GoRoute(
        path: RouteNames.languageSettings,
        builder: (context, state) => const LanguageSettingsPage(),
      ),
      GoRoute(
        path: RouteNames.legalPrivacy,
        builder: (context, state) =>
            const LegalPage(type: LegalDocumentType.privacyPolicy),
      ),
      GoRoute(
        path: RouteNames.legalTerms,
        builder: (context, state) =>
            const LegalPage(type: LegalDocumentType.termsConditions),
      ),
      GoRoute(
        path: RouteNames.legalMedicalDisclaimer,
        builder: (context, state) =>
            const LegalPage(type: LegalDocumentType.medicalDisclaimer),
      ),
      GoRoute(
        path: RouteNames.legalAiDisclaimer,
        builder: (context, state) =>
            const LegalPage(type: LegalDocumentType.aiDisclaimer),
      ),
      GoRoute(
        path: RouteNames.legalRefundPolicy,
        builder: (context, state) =>
            const LegalPage(type: LegalDocumentType.refundPolicy),
      ),
      GoRoute(
        path: RouteNames.support,
        builder: (context, state) => const SupportPage(),
      ),
      GoRoute(
        path: RouteNames.about,
        builder: (context, state) => const AboutPage(),
      ),
    ],
  );
});

class RouterRefreshNotifier extends ChangeNotifier {
  RouterRefreshNotifier(this.ref) {
    _subscription = ref.listen<AuthState>(
      authControllerProvider,
      (_, __) => notifyListeners(),
      fireImmediately: true,
    );
  }

  final Ref ref;
  late final ProviderSubscription<AuthState> _subscription;

  @override
  void dispose() {
    _subscription.close();
    super.dispose();
  }
}
