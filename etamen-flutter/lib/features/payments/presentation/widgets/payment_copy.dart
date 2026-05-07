import 'package:etamen_app/features/home/presentation/widgets/home_experience_widgets.dart';
import 'package:flutter/widgets.dart';

String friendlyAppointmentStatus(BuildContext context, String? value) {
  final normalized = value?.trim().toLowerCase();
  return switch (normalized) {
    'pending_payment' => uxCopy(
      context,
      'في انتظار اختيار طريقة الدفع',
      'Waiting for payment method',
    ),
    'pending_payment_review' => uxCopy(
      context,
      'إثبات الدفع قيد المراجعة',
      'Payment proof is under review',
    ),
    'confirmed' => uxCopy(context, 'تم تأكيد الموعد', 'Appointment confirmed'),
    'completed' => uxCopy(context, 'اكتمل الموعد', 'Appointment completed'),
    'cancelled' ||
    'canceled' => uxCopy(context, 'تم إلغاء الموعد', 'Appointment cancelled'),
    'no_show' => uxCopy(context, 'لم يتم حضور الموعد', 'Appointment missed'),
    null || '' => '-',
    _ => uxCopy(
      context,
      'حالة الموعد تحت المراجعة',
      'Appointment is under review',
    ),
  };
}

String friendlyPaymentMethodType(BuildContext context, String? value) {
  final normalized = value?.trim().toLowerCase();
  return switch (normalized) {
    'vodafone_cash' ||
    'vodafone' => uxCopy(context, 'فودافون كاش', 'Vodafone Cash'),
    'instapay' || 'insta_pay' => uxCopy(context, 'إنستاباي', 'InstaPay'),
    'paymob' ||
    'card' ||
    'bank_card' => uxCopy(context, 'بطاقة بنكية', 'Bank card'),
    null || '' => '-',
    _ => uxCopy(context, 'طريقة دفع مسجلة', 'Saved payment method'),
  };
}
