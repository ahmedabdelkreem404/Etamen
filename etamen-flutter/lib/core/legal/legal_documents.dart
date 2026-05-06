import 'package:etamen_app/core/legal/legal_document_type.dart';

class LegalDocument {
  const LegalDocument({
    required this.type,
    required this.titleAr,
    required this.titleEn,
    required this.sectionsAr,
    required this.sectionsEn,
  });

  final LegalDocumentType type;
  final String titleAr;
  final String titleEn;
  final List<String> sectionsAr;
  final List<String> sectionsEn;

  String title(String languageCode) {
    return languageCode == 'en' ? titleEn : titleAr;
  }

  List<String> sections(String languageCode) {
    return languageCode == 'en' ? sectionsEn : sectionsAr;
  }
}

class LegalDocuments {
  const LegalDocuments._();

  static const draftNoticeAr =
      'هذه نسخة أولية من النصوص القانونية ويجب مراجعتها قانونيًا قبل الإطلاق العام.';

  static const draftNoticeEn =
      'These legal texts are draft content and must be reviewed legally before public launch.';

  static const documents = <LegalDocument>[
    LegalDocument(
      type: LegalDocumentType.privacyPolicy,
      titleAr: 'سياسة الخصوصية',
      titleEn: 'Privacy Policy',
      sectionsAr: [
        draftNoticeAr,
        'يجمع تطبيق اطمن البيانات التي تدخلها بنفسك مثل بيانات الحساب، المواعيد، المدفوعات، طلبات الصيدلية والمعمل، القياسات الصحية، تذكيرات الأدوية، خطط المتابعة، والمحادثات داخل التطبيق.',
        'الملفات الطبية مثل الروشتات ونتائج المعامل وإثباتات الدفع تعتبر ملفات خاصة ولا يتم عرضها إلا للمستخدم أو الجهات المصرح لها من خلال الباكند.',
        'تستخدم البيانات لتقديم خدمات التطبيق وتشغيل الحجز والدفع والطلبات والتنبيهات وتجربة المساعد الذكي بشكل آمن ومحدود.',
        'لا يبيع اطمن بياناتك الشخصية أو الصحية. يجب حماية بيانات الدخول وعدم مشاركة الحساب أو الملفات الحساسة مع غير المصرح لهم.',
        'لأي سؤال متعلق بالخصوصية أو طلب متعلق بالبيانات، تواصل مع الدعم من صفحة الدعم داخل التطبيق.',
      ],
      sectionsEn: [
        draftNoticeEn,
        'Etamen collects data you enter, including account data, appointments, payments, pharmacy and lab orders, health tracking, medication reminders, care plans, and in-app conversations.',
        'Medical files such as prescriptions, lab results, and payment proofs are private and are only shown to the user or authorized parties through the backend.',
        'Data is used to provide app services, including booking, payments, orders, notifications, and safe AI assistant features.',
        'Etamen does not sell personal or health data. Keep your account and sensitive files protected.',
        'For privacy questions or data requests, contact support from the app support page.',
      ],
    ),
    LegalDocument(
      type: LegalDocumentType.termsConditions,
      titleAr: 'الشروط والأحكام',
      titleEn: 'Terms & Conditions',
      sectionsAr: [
        draftNoticeAr,
        'باستخدام اطمن، يلتزم المستخدم بإدخال بيانات صحيحة وعدم إساءة استخدام الخدمات أو انتحال شخصية أي مستخدم أو مقدم خدمة.',
        'مقدمو الخدمة مستقلون عن التطبيق، وتأكيد المواعيد والطلبات قد يعتمد على موافقة مقدم الخدمة أو الإدارة حسب حالة الخدمة.',
        'مراجعة المدفوعات اليدوية قد تستغرق وقتًا، ولا يعتبر رفع إثبات الدفع تأكيدًا نهائيًا إلا بعد تحقق الباكند والإدارة.',
        'قد يؤدي سوء الاستخدام، إرسال بيانات مضللة، أو محاولة الوصول إلى بيانات غير مصرح بها إلى تعليق الحساب.',
        'بعض الخدمات قد تكون تجريبية أو مؤجلة قبل الإطلاق العام، وسيتم توضيح القيود داخل التطبيق عند الحاجة.',
      ],
      sectionsEn: [
        draftNoticeEn,
        'By using Etamen, users agree to enter accurate data and avoid misuse, impersonation, or unauthorized access.',
        'Providers are independent, and appointments or orders may depend on provider or admin confirmation.',
        'Manual payment verification may take time. Uploading proof is not final confirmation until the backend/admin verifies it.',
        'Misuse, misleading data, or unauthorized access attempts may lead to account suspension.',
        'Some services may be experimental or deferred before public launch, and limitations are shown when relevant.',
      ],
    ),
    LegalDocument(
      type: LegalDocumentType.medicalDisclaimer,
      titleAr: 'إخلاء المسؤولية الطبية',
      titleEn: 'Medical Disclaimer',
      sectionsAr: [
        draftNoticeAr,
        'التطبيق لا يغني عن الطبيب ولا يعتبر بديلًا للكشف أو الاستشارة الطبية المتخصصة.',
        'القياسات الصحية وخطط المتابعة وتذكيرات الأدوية للتنظيم والمتابعة فقط، ولا تعتبر تشخيصًا أو علاجًا طبيًا.',
        'لا توقف أو تغير جرعة أي دواء ولا تبدأ علاجًا جديدًا بناءً على بيانات التطبيق بدون الرجوع للطبيب أو الصيدلي.',
        'إذا ظهرت أعراض خطيرة مثل ألم صدر شديد، ضيق تنفس، فقدان وعي، نزيف شديد، ضعف مفاجئ، أو أفكار لإيذاء النفس، تواصل مع الطوارئ فورًا.',
        'أي قرار طبي يجب أن يتم مع طبيب مختص وبناءً على تقييم سريري مناسب.',
      ],
      sectionsEn: [
        draftNoticeEn,
        'The app does not replace a doctor and is not a substitute for professional medical consultation or examination.',
        'Health tracking, care plans, and medication reminders are for organization and follow-up only, not diagnosis or treatment.',
        'Do not stop, change dosage, or start any medication based on app data without consulting a doctor or pharmacist.',
        'For severe symptoms such as chest pain, shortness of breath, loss of consciousness, heavy bleeding, sudden weakness, or self-harm thoughts, contact emergency services immediately.',
        'Medical decisions must be made with a qualified clinician after proper evaluation.',
      ],
    ),
    LegalDocument(
      type: LegalDocumentType.aiDisclaimer,
      titleAr: 'إخلاء مسؤولية المساعد الذكي',
      titleEn: 'AI Assistant Disclaimer',
      sectionsAr: [
        draftNoticeAr,
        'المساعد الذكي ليس طبيبًا ولا يقدم تشخيصًا أو وصف علاج أو وصفات طبية.',
        'دوره هو مساعدتك في تنظيم المعلومات وفهمها بشكل عام، وليس اتخاذ قرارات طبية أو تغيير دواء أو جرعة.',
        'قد يرفض المساعد بعض الأسئلة الطبية غير الآمنة، وقد يعرض إرشادًا طارئًا عند وجود أعراض خطيرة.',
        'قد يكون المساعد غير متاح مؤقتًا حسب إعدادات الباكند أو مزود الخدمة، ولا يجب الاعتماد عليه في الحالات العاجلة.',
        'القرار الطبي يجب أن يكون مع طبيب مختص، وأي أعراض خطيرة تستدعي التواصل مع الطوارئ فورًا.',
      ],
      sectionsEn: [
        draftNoticeEn,
        'The AI assistant is not a doctor and does not provide diagnosis, treatment, prescriptions, or medication changes.',
        'It can help organize and understand information generally, but it is not a medical decision-maker.',
        'The assistant may refuse unsafe medical requests and may show emergency guidance for red-flag symptoms.',
        'The assistant may be temporarily unavailable depending on backend/provider configuration and must not be used for emergencies.',
        'Medical decisions belong with qualified clinicians, and severe symptoms require emergency care immediately.',
      ],
    ),
    LegalDocument(
      type: LegalDocumentType.refundPolicy,
      titleAr: 'سياسة الاسترداد والإلغاء',
      titleEn: 'Refund / Cancellation Policy',
      sectionsAr: [
        draftNoticeAr,
        'أتمتة الاسترداد غير مفعلة حاليًا داخل التطبيق. أي طلب استرداد أو إلغاء مدفوع يحتاج مراجعة من الدعم أو الإدارة.',
        'إلغاء خدمة مدفوعة قد يعتمد على حالة الموعد أو الطلب، وسياسة مقدم الخدمة، وقرار الإدارة بعد مراجعة التفاصيل.',
        'المدفوعات اليدوية تحتاج رفع إثبات ومراجعة قبل اعتماد الدفع، ولا يجب اعتبار التحويل مؤكدًا من التطبيق قبل التحقق.',
        'حالة Paymob أو أي بوابة دفع يتم تأكيدها من الباكند فقط، ولا يتم الوثوق بالرجوع من صفحة الدفع كدليل نهائي.',
        'للطلبات المتعلقة بالاسترداد أو الإلغاء، تواصل مع الدعم موضحًا رقم الموعد أو الطلب وبيانات الدفع المتاحة.',
      ],
      sectionsEn: [
        draftNoticeEn,
        'Refund automation is not currently implemented in the app. Paid cancellations or refund requests require support/admin review.',
        'Eligibility may depend on appointment/order status, provider policy, and admin review.',
        'Manual payments require proof upload and review before verification.',
        'Paymob or gateway status is confirmed by the backend only; returning from checkout is not final proof.',
        'For refund or cancellation requests, contact support with the appointment/order number and available payment details.',
      ],
    ),
  ];

  static LegalDocument byType(LegalDocumentType type) {
    return documents.firstWhere((document) => document.type == type);
  }
}
