import 'package:flutter/material.dart';

class AppLocalizations {
  AppLocalizations(this.locale);

  final Locale locale;

  static const supportedLocales = [Locale('ar'), Locale('en')];

  static const LocalizationsDelegate<AppLocalizations> delegate =
      _AppLocalizationsDelegate();

  static AppLocalizations of(BuildContext context) {
    return Localizations.of<AppLocalizations>(context, AppLocalizations)!;
  }

  bool get isArabic => locale.languageCode == 'ar';

  String get(String key) {
    return _values[locale.languageCode]?[key] ?? _values['ar']?[key] ?? key;
  }

  static const _values = <String, Map<String, String>>{
    'ar': {
      'appName': 'اطمّن',
      'login': 'تسجيل الدخول',
      'register': 'إنشاء حساب',
      'email': 'البريد الإلكتروني',
      'password': 'كلمة المرور',
      'name': 'الاسم',
      'phone': 'رقم الهاتف',
      'confirmPassword': 'تأكيد كلمة المرور',
      'requiredField': 'هذا الحقل مطلوب',
      'invalidEmail': 'اكتب بريدًا إلكترونيًا صحيحًا',
      'passwordMin': 'كلمة المرور يجب ألا تقل عن 8 أحرف',
      'passwordMismatch': 'كلمتا المرور غير متطابقتين',
      'home': 'الرئيسية',
      'doctors': 'الأطباء',
      'account': 'الحساب',
      'logout': 'تسجيل الخروج',
      'retry': 'إعادة المحاولة',
      'loading': 'جاري التحميل...',
      'emptyDoctors': 'لا يوجد أطباء متاحون حاليًا',
      'emptySlots': 'لا توجد مواعيد متاحة',
      'serverUnavailable': 'الاتصال بالسيرفر غير متاح حاليًا',
      'unexpectedError': 'حدث خطأ غير متوقع',
      'sessionExpired': 'انتهت الجلسة، سجل دخول مرة أخرى',
      'forbidden': 'ليس لديك صلاحية لتنفيذ هذا الإجراء',
      'rateLimited': 'محاولات كثيرة، حاول بعد قليل',
      'bookAppointment': 'احجز موعد',
      'availableSlots': 'المواعيد المتاحة',
      'consultationType': 'نوع الكشف',
      'clinic': 'في العيادة',
      'online': 'أونلاين',
      'problemDescription': 'وصف المشكلة اختياري',
      'confirmBooking': 'تأكيد الحجز',
      'bookingConfirmed': 'تم تأكيد الحجز',
      'bookingPendingPayment': 'الحجز في انتظار الدفع',
      'paymentLater': 'الدفع سيتم تفعيله في Sprint لاحق',
      'slotNoLongerAvailable': 'الموعد لم يعد متاحًا، اختر موعدًا آخر',
      'fee': 'الكشف',
      'rating': 'التقييم',
      'specialties': 'التخصصات',
      'language': 'اللغة',
      'arabic': 'العربية',
      'english': 'English',
    },
    'en': {
      'appName': 'Etamen',
      'login': 'Login',
      'register': 'Create account',
      'email': 'Email',
      'password': 'Password',
      'name': 'Name',
      'phone': 'Phone',
      'confirmPassword': 'Confirm password',
      'requiredField': 'This field is required',
      'invalidEmail': 'Enter a valid email',
      'passwordMin': 'Password must be at least 8 characters',
      'passwordMismatch': 'Passwords do not match',
      'home': 'Home',
      'doctors': 'Doctors',
      'account': 'Account',
      'logout': 'Logout',
      'retry': 'Retry',
      'loading': 'Loading...',
      'emptyDoctors': 'No doctors are available right now',
      'emptySlots': 'No available slots',
      'serverUnavailable': 'Cannot connect to the server',
      'unexpectedError': 'Something went wrong',
      'sessionExpired': 'Session expired, please login again',
      'forbidden': 'You do not have permission',
      'rateLimited': 'Too many attempts, try again later',
      'bookAppointment': 'Book appointment',
      'availableSlots': 'Available slots',
      'consultationType': 'Consultation type',
      'clinic': 'Clinic',
      'online': 'Online',
      'problemDescription': 'Problem description optional',
      'confirmBooking': 'Confirm booking',
      'bookingConfirmed': 'Booking confirmed',
      'bookingPendingPayment': 'Booking is pending payment',
      'paymentLater': 'Payment will be enabled in a later sprint',
      'slotNoLongerAvailable': 'This slot is no longer available',
      'fee': 'Fee',
      'rating': 'Rating',
      'specialties': 'Specialties',
      'language': 'Language',
      'arabic': 'العربية',
      'english': 'English',
    },
  };
}

class _AppLocalizationsDelegate
    extends LocalizationsDelegate<AppLocalizations> {
  const _AppLocalizationsDelegate();

  @override
  bool isSupported(Locale locale) {
    return AppLocalizations.supportedLocales
        .map((item) => item.languageCode)
        .contains(locale.languageCode);
  }

  @override
  Future<AppLocalizations> load(Locale locale) async {
    return AppLocalizations(locale);
  }

  @override
  bool shouldReload(covariant LocalizationsDelegate<AppLocalizations> old) {
    return false;
  }
}
