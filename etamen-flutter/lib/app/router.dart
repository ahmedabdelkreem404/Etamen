import 'package:etamen_app/core/routing/route_names.dart';
import 'package:etamen_app/features/account/presentation/pages/account_page.dart';
import 'package:etamen_app/features/appointments/presentation/pages/appointment_booking_page.dart';
import 'package:etamen_app/features/appointments/presentation/pages/appointment_result_page.dart';
import 'package:etamen_app/features/auth/presentation/pages/login_page.dart';
import 'package:etamen_app/features/auth/presentation/pages/register_page.dart';
import 'package:etamen_app/features/auth/presentation/providers/auth_controller.dart';
import 'package:etamen_app/features/doctors/presentation/pages/doctor_profile_page.dart';
import 'package:etamen_app/features/doctors/presentation/pages/doctors_list_page.dart';
import 'package:etamen_app/features/home/presentation/pages/home_page.dart';
import 'package:etamen_app/features/payments/presentation/pages/manual_payment_page.dart';
import 'package:etamen_app/features/payments/presentation/pages/payment_page.dart';
import 'package:etamen_app/features/payments/presentation/pages/payment_status_page.dart';
import 'package:etamen_app/features/payments/presentation/pages/paymob_checkout_page.dart';
import 'package:etamen_app/features/splash/presentation/pages/splash_page.dart';
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
        path: RouteNames.doctors,
        builder: (context, state) => const DoctorsListPage(),
      ),
      GoRoute(
        path: '/doctors/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return DoctorProfilePage(doctorId: id ?? 0);
        },
      ),
      GoRoute(
        path: '/doctors/:id/booking',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          return AppointmentBookingPage(doctorId: id ?? 0);
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
        path: '/payments/:id',
        builder: (context, state) {
          final id = int.tryParse(state.pathParameters['id'] ?? '');
          final appointmentId = int.tryParse(
            state.uri.queryParameters['appointmentId'] ?? '',
          );
          return PaymentPage(paymentId: id ?? 0, appointmentId: appointmentId);
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
          return ManualPaymentPage(
            paymentId: id ?? 0,
            methodId: methodId ?? 0,
            appointmentId: appointmentId,
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
          return PaymentStatusPage(
            paymentId: id ?? 0,
            appointmentId: appointmentId,
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
          return PaymobCheckoutPage(
            paymentId: id ?? 0,
            appointmentId: appointmentId,
          );
        },
      ),
      GoRoute(
        path: RouteNames.account,
        builder: (context, state) => const AccountPage(),
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
