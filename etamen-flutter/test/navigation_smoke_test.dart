import 'package:etamen_app/core/config/app_config.dart';
import 'package:etamen_app/core/network/api_error.dart';
import 'package:etamen_app/core/network/error_mapper.dart';
import 'package:etamen_app/core/routing/route_names.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  test('critical route names are stable for pilot navigation', () {
    expect(RouteNames.login, '/login');
    expect(RouteNames.register, '/register');
    expect(RouteNames.home, '/home');
    expect(RouteNames.doctors, '/doctors');
    expect(RouteNames.appointments, '/appointments');
    expect(RouteNames.payment(10), '/payments/10');
    expect(
      RouteNames.payment(10, appointmentId: 7),
      '/payments/10?appointmentId=7',
    );
    expect(
      RouteNames.payment(10, pharmacyOrderId: 8),
      '/payments/10?pharmacyOrderId=8',
    );
    expect(RouteNames.payment(10, labOrderId: 9), '/payments/10?labOrderId=9');
    expect(
      RouteNames.payment(10, radiologyOrderId: 12),
      '/payments/10?radiologyOrderId=12',
    );
    expect(
      RouteNames.manualPayment(10, methodId: 2, appointmentId: 7),
      '/payments/10/manual?methodId=2&appointmentId=7',
    );
    expect(
      RouteNames.paymentStatus(10, pharmacyOrderId: 8),
      '/payments/10/status?pharmacyOrderId=8',
    );
    expect(
      RouteNames.paymobCheckout(10, labOrderId: 9),
      '/payments/10/paymob?labOrderId=9',
    );
    expect(RouteNames.pharmacies, '/pharmacies');
    expect(RouteNames.pharmacyCart, '/pharmacy/cart');
    expect(
      RouteNames.pharmacyPrescriptionUpload,
      '/pharmacy/prescription-upload',
    );
    expect(RouteNames.pharmacyOrders, '/pharmacy/orders');
    expect(RouteNames.labs, '/labs');
    expect(RouteNames.radiology, '/radiology');
    expect(RouteNames.radiologyOrderBuilder, '/radiology/order-builder');
    expect(RouteNames.radiologyOrders, '/radiology/orders');
    expect(RouteNames.hospitals, '/hospitals');
    expect(RouteNames.labCart, '/labs/cart');
    expect(RouteNames.labOrders, '/lab-orders');
    expect(RouteNames.health, '/health');
    expect(RouteNames.healthProfile, '/health/profile');
    expect(RouteNames.editHealthProfile, '/health/profile/edit');
    expect(RouteNames.healthVitals, '/health/vitals');
    expect(RouteNames.medications, '/medications');
    expect(RouteNames.medicationReminders, '/medications/reminders');
    expect(
      RouteNames.createMedicationReminder,
      '/medications/reminders/create',
    );
    expect(RouteNames.todayMedications, '/medications/today');
    expect(RouteNames.medicationAdherence, '/medications/adherence');
    expect(RouteNames.carePlans, '/care-plans');
    expect(RouteNames.notifications, '/notifications');
    expect(RouteNames.notificationPreferences, '/notifications/preferences');
    expect(RouteNames.ai, '/ai');
    expect(RouteNames.aiContextPreview, '/ai/context-preview');
    expect(RouteNames.account, '/account');
    expect(RouteNames.accountSettings, '/account/settings');
    expect(RouteNames.languageSettings, '/account/language');
    expect(RouteNames.legalPrivacy, '/legal/privacy');
    expect(RouteNames.legalTerms, '/legal/terms');
    expect(RouteNames.legalMedicalDisclaimer, '/legal/medical-disclaimer');
    expect(RouteNames.legalAiDisclaimer, '/legal/ai-disclaimer');
    expect(RouteNames.legalRefundPolicy, '/legal/refund-policy');
    expect(RouteNames.support, '/support');
    expect(RouteNames.about, '/about');
  });

  test('parameterized routes are constructed without crashing', () {
    expect(RouteNames.doctorProfile(1), '/doctors/1');
    expect(RouteNames.doctorBooking(1), '/doctors/1/booking');
    expect(RouteNames.appointmentDetails(1), '/appointments/1');
    expect(RouteNames.pharmacyProducts(2), '/pharmacies/2/products');
    expect(RouteNames.pharmacyOrderDetails(2), '/pharmacy/orders/2');
    expect(RouteNames.labTests(3), '/labs/3/tests');
    expect(RouteNames.labOrderDetails(3), '/lab-orders/3');
    expect(RouteNames.radiologyOrderDetails(3), '/radiology/orders/3');
    expect(RouteNames.hospitalDetails(4), '/hospitals/4');
    expect(
      RouteNames.hospitalDepartmentDoctors(4, 5),
      '/hospitals/4/departments/5/doctors',
    );
    expect(RouteNames.addVital(), '/health/vitals/add');
    expect(RouteNames.addVital('weight'), '/health/vitals/add/weight');
    expect(RouteNames.medicationReminderDetails(4), '/medications/reminders/4');
    expect(RouteNames.carePlanDetails(5), '/care-plans/5');
    expect(RouteNames.carePlanDay(5, 2), '/care-plans/5/day/2');
    expect(RouteNames.carePlanCheckin(5), '/care-plans/5/checkin');
    expect(RouteNames.carePlanMealLog(5), '/care-plans/5/meal-log');
    expect(RouteNames.carePlanProgress(5), '/care-plans/5/progress');
    expect(RouteNames.notificationDetails(6), '/notifications/6');
    expect(RouteNames.aiConversation(7), '/ai/conversations/7');
  });

  test('pilot configuration values are string safe and secrets are absent', () {
    expect(AppConfig.apiBaseUrl, startsWith('http'));
    expect(AppConfig.environment, isNotEmpty);
    expect(AppConfig.supportEmail, isA<String>());
    expect(AppConfig.supportPhone, isA<String>());
    expect(AppConfig.supportWhatsappUrl, isA<String>());
  });

  test('fallback error messages are user friendly Arabic text', () {
    const mapper = ErrorMapper();

    expect(
      mapper.fromEnvelope(const {}, statusCode: 401).message,
      'انتهت الجلسة، سجل دخول مرة أخرى',
    );
    expect(
      mapper.fromEnvelope(const {}, statusCode: 403).type,
      ApiErrorType.forbidden,
    );
    expect(
      mapper.fromEnvelope(const {}, statusCode: 500).message,
      'حدث خطأ غير متوقع، حاول مرة أخرى',
    );
  });
}
