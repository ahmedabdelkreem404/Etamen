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
      'appName': 'اطمن',
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
      'refresh': 'تحديث',
      'loading': 'جاري التحميل...',
      'emptyDoctors': 'لا يوجد أطباء متاحون حاليًا',
      'emptySlots': 'لا توجد مواعيد متاحة',
      'serverUnavailable': 'الاتصال بالسيرفر غير متاح حاليًا',
      'unexpectedError': 'حدث خطأ غير متوقع',
      'sessionExpired': 'انتهت الجلسة، سجل دخول مرة أخرى',
      'forbidden': 'ليس لديك صلاحية لتنفيذ هذا الإجراء',
      'rateLimited': 'طلبات كثيرة، حاول بعد قليل',
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
      'paymentMethods': 'طرق الدفع',
      'choosePaymentMethod': 'اختر طريقة الدفع',
      'paymentMethod': 'طريقة الدفع',
      'manualPaymentSubtitle': 'حوّل المبلغ ثم ارفع إثبات الدفع للمراجعة',
      'paymobSubtitle': 'دفع إلكتروني عبر Paymob من خلال الباكند',
      'paymentSummary': 'ملخص الدفع',
      'paymentAdminReviewNotice':
          'الدفع اليدوي تتم مراجعته من الإدارة قبل تأكيد الموعد.',
      'emptyPaymentMethods': 'لا توجد طرق دفع متاحة حاليًا',
      'paymentStatusUnavailable': 'تعذر تحميل حالة الدفع الآن',
      'appointmentStatus': 'حالة الموعد',
      'goToPayment': 'اذهب للدفع',
      'bookingPaymentMissing':
          'تم إنشاء الحجز لكن لم يتم العثور على بيانات الدفع، حاول تحديث الحالة أو تواصل مع الدعم',
      'amount': 'المبلغ',
      'manualInstructions': 'تعليمات الدفع',
      'instructionsUnavailable': 'تعليمات الدفع غير متاحة حاليًا',
      'uploadProof': 'رفع إثبات الدفع',
      'proofUploadHint': 'اختر صورة واضحة للتحويل. لا يتم حفظ المسار المحلي.',
      'chooseProofImage': 'اختيار صورة الإثبات',
      'removeFile': 'إزالة الملف',
      'referenceNumber': 'رقم العملية اختياري',
      'senderPhone': 'رقم المحوّل اختياري',
      'notes': 'ملاحظات',
      'submitProof': 'إرسال الإثبات',
      'proofRequired': 'اختر صورة إثبات الدفع أولًا',
      'fileTooLarge': 'حجم الملف كبير، اختر صورة أصغر',
      'proofUploaded': 'تم رفع إثبات الدفع، سيتم مراجعته قريبًا',
      'doNotCloseBeforeProof': 'لا تغلق التطبيق قبل رفع إثبات الدفع',
      'paymentStatus': 'حالة الدفع',
      'checkPaymentStatus': 'تحقق من حالة الدفع',
      'paymentPollingMessage':
          'نراجع حالة الدفع كل بضع ثوانٍ بدون إزعاج السيرفر.',
      'waitingAdminReview': 'في انتظار مراجعة الإدارة لإثبات الدفع.',
      'waitingGatewayConfirmation': 'في انتظار تأكيد بوابة الدفع من الباكند.',
      'paymentAndAppointmentConfirmed': 'تم تأكيد الدفع والموعد',
      'paymentRejectedRetry': 'تم رفض إثبات الدفع، يمكنك إعادة المحاولة',
      'retryProof': 'إعادة رفع إثبات الدفع',
      'viewAppointment': 'عرض الموعد',
      'backHome': 'العودة للرئيسية',
      'lastUpdated': 'آخر تحديث',
      'onlinePayment': 'دفع إلكتروني',
      'paymobCheckoutTitle': 'الدفع الإلكتروني عبر Paymob',
      'paymobCheckoutSafety':
          'سيتم فتح صفحة الدفع الآمنة التي أنشأها الباكند فقط.',
      'paymobRedirectNotProof':
          'الرجوع من صفحة الدفع ليس دليلًا على النجاح؛ سنراجع حالة الدفع من الباكند.',
      'openPaymentPage': 'افتح صفحة الدفع',
      'createPaymobSession': 'إنشاء جلسة الدفع',
      'paymobMissingCheckoutUrl':
          'الباكند لم يرجع رابط checkout. قد يحتاج إعداد Paymob الحقيقي.',
      'cannotOpenPaymentPage': 'تعذر فتح صفحة الدفع',
      'paymentDraft': 'مسودة',
      'awaitingMethod': 'اختر طريقة الدفع',
      'awaitingProof': 'بانتظار إثبات الدفع',
      'pendingReview': 'في انتظار المراجعة',
      'pendingGateway': 'جاري الدفع الإلكتروني',
      'paymentVerified': 'تم تأكيد الدفع',
      'paymentRejected': 'تم رفض الدفع',
      'paymentFailed': 'فشل الدفع',
      'paymentExpired': 'انتهت صلاحية الدفع',
      'paymentCancelled': 'تم إلغاء الدفع',
      'paymentRefunded': 'تم الاسترداد',
      'unknownStatus': 'حالة غير معروفة',
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
      'refresh': 'Refresh',
      'loading': 'Loading...',
      'emptyDoctors': 'No doctors are available right now',
      'emptySlots': 'No available slots',
      'serverUnavailable': 'Cannot connect to the server',
      'unexpectedError': 'Something went wrong',
      'sessionExpired': 'Session expired, please login again',
      'forbidden': 'You do not have permission',
      'rateLimited': 'Too many requests, try again later',
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
      'paymentMethods': 'Payment methods',
      'choosePaymentMethod': 'Choose payment method',
      'paymentMethod': 'Payment method',
      'manualPaymentSubtitle': 'Transfer then upload proof for admin review',
      'paymobSubtitle': 'Online payment through backend-created Paymob session',
      'paymentSummary': 'Payment summary',
      'paymentAdminReviewNotice':
          'Manual payments are reviewed by admin before confirming the appointment.',
      'emptyPaymentMethods': 'No payment methods are available now',
      'paymentStatusUnavailable': 'Payment status is unavailable now',
      'appointmentStatus': 'Appointment status',
      'goToPayment': 'Go to payment',
      'bookingPaymentMissing':
          'Booking was created but payment data was not found. Refresh status or contact support.',
      'amount': 'Amount',
      'manualInstructions': 'Payment instructions',
      'instructionsUnavailable': 'Payment instructions are unavailable',
      'uploadProof': 'Upload payment proof',
      'proofUploadHint':
          'Choose a clear transfer screenshot. The local path is not stored.',
      'chooseProofImage': 'Choose proof image',
      'removeFile': 'Remove file',
      'referenceNumber': 'Reference number optional',
      'senderPhone': 'Sender phone optional',
      'notes': 'Notes',
      'submitProof': 'Submit proof',
      'proofRequired': 'Choose a payment proof image first',
      'fileTooLarge': 'File is too large, choose a smaller image',
      'proofUploaded': 'Payment proof uploaded and will be reviewed soon',
      'doNotCloseBeforeProof': 'Do not close the app before uploading proof',
      'paymentStatus': 'Payment status',
      'checkPaymentStatus': 'Check payment status',
      'paymentPollingMessage':
          'We check payment status every few seconds without spamming the server.',
      'waitingAdminReview': 'Waiting for admin review.',
      'waitingGatewayConfirmation':
          'Waiting for gateway confirmation from backend.',
      'paymentAndAppointmentConfirmed': 'Payment and appointment confirmed',
      'paymentRejectedRetry': 'Payment proof was rejected. You can try again.',
      'retryProof': 'Upload proof again',
      'viewAppointment': 'View appointment',
      'backHome': 'Back home',
      'lastUpdated': 'Last updated',
      'onlinePayment': 'Online payment',
      'paymobCheckoutTitle': 'Paymob online payment',
      'paymobCheckoutSafety':
          'Only the backend-created checkout page will be opened.',
      'paymobRedirectNotProof':
          'Returning from checkout is not proof of payment; backend status remains the source of truth.',
      'openPaymentPage': 'Open payment page',
      'createPaymobSession': 'Create payment session',
      'paymobMissingCheckoutUrl':
          'Backend did not return a checkout URL. Real Paymob config may be missing.',
      'cannotOpenPaymentPage': 'Cannot open payment page',
      'paymentDraft': 'Draft',
      'awaitingMethod': 'Awaiting method',
      'awaitingProof': 'Awaiting proof',
      'pendingReview': 'Pending review',
      'pendingGateway': 'Pending gateway',
      'paymentVerified': 'Payment verified',
      'paymentRejected': 'Payment rejected',
      'paymentFailed': 'Payment failed',
      'paymentExpired': 'Payment expired',
      'paymentCancelled': 'Payment cancelled',
      'paymentRefunded': 'Payment refunded',
      'unknownStatus': 'Unknown status',
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
