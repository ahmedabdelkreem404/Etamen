# Client Demo Talk Track

سكريبت شفهي مقترح لديمو 10 دقائق. استخدمه بثقة ووضوح، بدون وعود إطلاق.

## 0:00 - Opening

"اطمن — كل صحتك في مكان واحد. اللي هتشوفوه دلوقتي هو ديمو محلي داخلي بيعرض رؤية المنتج وتجربة المريض والمزود والأدمن. مهم أوضح من البداية: ده مش production، مش staging، ومفيش مستخدمين حقيقيين أو دفع حي."

## 1:00 - Patient booking

"هبدأ بتجربة المريض. المستخدم يدخل، يشوف الخدمات، يفتح الأطباء، يختار دكتور وميعاد. الهدف هنا إن رحلة الحجز تبقى واضحة ومباشرة."

"السعر وحالة الحجز لا يتم اعتمادهم من Flutter. الـ backend هو مصدر الحقيقة."

## 3:00 - Payment proof

"هنا بنستخدم manual payment proof. المريض يختار Vodafone Cash أو InstaPay ويرفع صورة إثبات. التطبيق لا يؤكد الدفع بنفسه. الحالة تفضل pending review لحد ما الأدمن يراجع."

"ده proof-of-flow محلي، وليس دفع live."

## 4:00 - Other patient services

"بنفس الأسلوب، الديمو المحلي يغطي radiology، gym، coach، ومعاهم support/refund/dispute foundation. النتائج الطبية تظهر metadata أو download آمن فقط، من غير تفسير طبي."

## 5:30 - Provider workspace

"نفس التطبيق يقدر يفتح provider workspace حسب صلاحيات المستخدم. المزود يشوف dashboard وoperations MVP. الأهم إن Flutter لا يخترع الصلاحيات، كل permission جاي من backend."

"لو staff محدود، backend يمنعه من أي action غير مصرح بها."

## 7:00 - Admin operations

"هنا Platform Admin Operations Center. الأدمن يشوف payment reviews، provider approvals، support tickets، refunds، disputes، وaudit log."

"الإثباتات والمستندات تظهر كـ metadata آمنة فقط. لا نعرض raw proof path ولا private provider documents."

## 8:30 - Support / refund / dispute

"الدعم والاسترداد والنزاعات موجودين كfoundation تشغيلية. الاسترداد هنا قرار منصة يدوي، وليس gateway refund حي. كل إجراء مهم بيتسجل في audit log."

## 9:15 - Safety and medical disclaimer

"Etamen لا يستخدم للطوارئ، ولا يشخص، ولا يصف علاج، ولا يستبدل الطبيب. أي استخدام حقيقي يحتاج مراجعة قانونية وخصوصية وتشغيلية قبل pilot."

## 9:45 - Honest closing

"الخلاصة: المنتج قوي ومتماسك كديمو محلي، ويثبت patient/provider/admin model. الخطوة التقنية الحقيقية القادمة هي server access ثم staging deployment آمن بعد backup وreadiness checks. لحد ما ده يحصل، لا ندعي جاهزية إطلاق."
