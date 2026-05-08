@php
    $isEnglish = request('lang') === 'en';
    $lang = $isEnglish ? 'en' : 'ar';
    $dir = $isEnglish ? 'ltr' : 'rtl';
    $switchUrl = url('/') . ($isEnglish ? '' : '?lang=en');
    $switchLabel = $isEnglish ? 'العربية' : 'English';
    $legacyHeroPath = public_path('legacy-doctorfinder/doctor-finder-hero.jpg');
    $legacyHeroUrl = is_file($legacyHeroPath)
        ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($legacyHeroPath))
        : asset('legacy-doctorfinder/doctor-finder-hero.jpg');
    $copy = $isEnglish ? [
        'title' => 'Etamen | Find A Doctor',
        'siteLabel' => 'Site',
        'siteText' => 'Etamen pilot services for patients',
        'supportLabel' => 'Support',
        'supportText' => 'Supervised pilot activation',
        'brand' => 'Etamen',
        'navHome' => 'Home',
        'navAbout' => 'About Us',
        'navSpecialist' => 'Specialist',
        'navDoctors' => 'Doctors',
        'doctorCta' => 'Join As Doctor +',
        'heroTitle' => 'Find A Doctor!',
        'heroLead' => 'Etamen keeps the old Doctor Finder feeling: clear search, medical teal colors, white cards, and a simple booking path from the first screen.',
        'searchLabel' => 'Start doctor search',
        'searchText' => 'Ex. Doctor Name',
        'category' => 'CATEGORY',
        'servicesLabel' => 'Etamen services',
        'serviceDoctor' => 'Find a doctor',
        'serviceDoctorText' => 'Search by specialty, view a clean profile, and choose a slot without friction.',
        'servicePharmacy' => 'Pharmacy orders',
        'servicePharmacyText' => 'Medicine and prescription requests with a clean card-based experience.',
        'serviceLabs' => 'Lab tests',
        'serviceLabsText' => 'Pick labs and tests with a patient-friendly order status.',
        'serviceHealth' => 'Health tracking',
        'serviceHealthText' => 'Vitals and reminders without a heavy admin look.',
        'flowTitle' => 'A booking flow closer to the old app',
        'flowText' => 'This page does not add new features. It focuses on restoring the visual quality: medical header, doctor cards with safe placeholders, and short booking steps.',
        'step1' => 'Search by doctor name or specialty.',
        'step2' => 'Review profile, fee, and location.',
        'step3' => 'Choose a slot, then upload payment proof when needed.',
        'mockTitle' => 'Mock app screens',
        'mockText' => 'Simple phone cards show the visual direction without embedding product screenshots.',
        'finalTitle' => 'Ready for visual product review',
        'finalText' => 'This is a lightweight pilot landing page, not a CMS, payment portal, or web registration flow. Public launch still needs final licensed assets, content, SEO, and legal review.',
        'pilotButton' => 'Request pilot review',
    ] : [
        'title' => 'اطمن | ابحث عن طبيب',
        'siteLabel' => 'الموقع',
        'siteText' => 'خدمات اطمن التجريبية للمرضى',
        'supportLabel' => 'الدعم',
        'supportText' => 'تفعيل تجريبي تحت المراجعة',
        'brand' => 'اطمن',
        'navHome' => 'الرئيسية',
        'navAbout' => 'عن اطمن',
        'navSpecialist' => 'التخصصات',
        'navDoctors' => 'الأطباء',
        'doctorCta' => '+ انضم كطبيب',
        'heroTitle' => 'ابحث عن طبيب',
        'heroLead' => 'واجهة اطمن الجديدة تحافظ على إحساس Doctor Finder القديم: بحث واضح، ألوان طبية هادئة، كروت بيضاء، وحجز بسيط من أول شاشة.',
        'searchLabel' => 'ابدأ البحث عن طبيب',
        'searchText' => 'مثال: اسم الطبيب',
        'category' => 'التخصصات',
        'servicesLabel' => 'خدمات اطمن',
        'serviceDoctor' => 'ابحث عن طبيب',
        'serviceDoctorText' => 'بحث بالتخصص، بروفايل واضح، واختيار موعد بدون تعقيد.',
        'servicePharmacy' => 'طلبات الصيدلية',
        'servicePharmacyText' => 'طلبات أدوية وروشتات بتجربة كروت بيضاء ونظيفة.',
        'serviceLabs' => 'تحاليل المعامل',
        'serviceLabsText' => 'اختيار معامل وتحاليل مع حالة طلب مفهومة للمريض.',
        'serviceHealth' => 'متابعة صحية',
        'serviceHealthText' => 'متابعة قياسات وتنبيهات بدون شكل إداري ثقيل.',
        'flowTitle' => 'تجربة حجز أقرب للتطبيق القديم',
        'flowText' => 'هذه الصفحة لا تضيف مزايا جديدة. التركيز هنا على جودة الواجهة: هيدر طبي، كروت أطباء بصورة آمنة أو placeholder، وخطوات حجز قصيرة.',
        'step1' => 'ابحث باسم الطبيب أو التخصص.',
        'step2' => 'راجع البروفايل والسعر والمكان.',
        'step3' => 'اختر الموعد ثم ارفع إثبات الدفع عند الحاجة.',
        'mockTitle' => 'نماذج شاشات التطبيق',
        'mockText' => 'كروت هاتف توضح الاتجاه البصري بدون نسخ screenshots داخل المنتج.',
        'finalTitle' => 'جاهز لمراجعة المنتج بصريًا',
        'finalText' => 'هذه صفحة تعريفية خفيفة للمراجعة التجريبية وليست CMS أو بوابة دفع أو تسجيل ويب. الإطلاق العام يحتاج محتوى وتسويق وصور نهائية مرخصة ومراجعة قانونية.',
        'pilotButton' => 'طلب مراجعة Pilot',
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $lang }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['title'] }}</title>
    <style>
        :root {
            --teal: #01d8c9;
            --teal-strong: #00b8af;
            --dark: #17242b;
            --panel: #203b46;
            --muted: #66737b;
            --accent: #0ea5a4;
            --accent-dark: #008c86;
            --mint: #e6fffc;
            --soft: #f7fafa;
            --border: #d7f4f0;
            --hero-bg: #e9fbf8;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            overflow-x: hidden;
        }

        body {
            margin: 0;
            color: var(--dark);
            background: #ffffff;
            font-family: Tahoma, Arial, sans-serif;
            line-height: 1.7;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .top-strip {
            background: #162131;
            color: #fff;
            font-size: 14px;
        }

        .top-strip .inner,
        .nav,
        .section {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
        }

        .top-strip .inner {
            min-height: 48px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
        }

        .accent {
            color: var(--accent);
            font-weight: 900;
        }

        .nav {
            min-height: 102px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            background: #fff;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 900;
            font-size: 26px;
        }

        .mark {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: var(--teal);
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 24px;
        }

        .links {
            display: flex;
            align-items: center;
            gap: 24px;
            color: #52606a;
            font-weight: 700;
        }

        .language-switch {
            border: 1px solid var(--border);
            color: var(--accent-dark);
            background: #fff;
            padding: 8px 14px;
            border-radius: 999px;
            font-weight: 900;
            white-space: nowrap;
        }

        .doctor-cta {
            background: var(--accent-dark);
            color: #fff;
            padding: 14px 26px;
            border-radius: 999px;
            font-weight: 900;
            box-shadow: 0 14px 26px rgba(0, 140, 134, 0.22);
        }

        .hero {
            min-height: 650px;
            background: var(--hero-bg);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: "";
            position: absolute;
            inset: 0 0 0 49%;
            background:
                linear-gradient(rgba(1, 216, 201, 0.10), rgba(0, 184, 175, 0.04)),
                url('{{ $legacyHeroUrl }}') center center / cover no-repeat;
            clip-path: polygon(9% 0, 100% 0, 100% 100%, 0 100%);
            filter: saturate(1.08);
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 180px;
            height: 460px;
            background: rgba(255, 255, 255, 0.20);
            transform: rotate(-18deg);
            top: 86px;
            right: 13%;
        }

        .hero .section {
            min-height: 650px;
            display: grid;
            grid-template-columns: minmax(280px, 520px) 1fr;
            align-items: center;
            position: relative;
            z-index: 1;
            direction: ltr;
        }

        .hero-copy {
            direction: rtl;
            text-align: right;
            justify-self: start;
            min-width: 0;
            max-width: 100%;
        }

        body.site-en .hero-copy,
        body.site-en .search-box {
            direction: ltr;
            text-align: left;
        }

        h1 {
            margin: 0;
            font-size: clamp(46px, 7vw, 78px);
            line-height: 1.05;
            font-weight: 900;
            letter-spacing: 0;
        }

        .lead {
            margin: 22px 0 26px;
            color: #5c676f;
            font-size: 20px;
            max-width: 520px;
        }

        .search-box {
            width: min(100%, 470px);
            min-height: 70px;
            border-radius: 999px;
            background: #fff;
            box-shadow: 0 18px 34px rgba(28, 42, 50, 0.12);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px 10px 12px;
            direction: rtl;
        }

        .search-box span {
            flex: 1;
            color: #697780;
            font-weight: 700;
        }

        .search-box span:first-child {
            min-width: 0;
        }

        .search-button {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: var(--accent-dark);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 26px;
            font-weight: 900;
        }

        .search-box .search-button {
            flex: 0 0 54px;
        }

        .category-kicker {
            margin-top: 44px;
            color: var(--accent-dark);
            font-weight: 900;
        }

        .hero-visual {
            justify-self: stretch;
            align-self: stretch;
            min-height: 520px;
            overflow: hidden;
            clip-path: polygon(12% 0, 100% 0, 100% 100%, 0 100%);
            position: relative;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.16);
        }

        .hero-visual img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: 35% center;
            display: block;
            filter: saturate(1.18) contrast(1.16);
        }

        .hero-visual::after {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, rgba(233, 251, 248, 0.04), rgba(1, 216, 201, 0.12));
            pointer-events: none;
        }

        .service-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-top: -70px;
            position: relative;
            z-index: 2;
        }

        .service {
            background: #fff;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 16px 36px rgba(24, 45, 54, 0.10);
            border: 1px solid var(--border);
            min-width: 0;
        }

        .service-icon {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: var(--mint);
            color: var(--teal-strong);
            display: grid;
            place-items: center;
            font-weight: 900;
            margin-bottom: 14px;
        }

        .service h2,
        .text-block h2,
        .phone-panel h2 {
            margin: 0 0 8px;
            font-size: 22px;
            line-height: 1.25;
        }

        .service p,
        .text-block p,
        .phone-panel p {
            margin: 0;
            color: var(--muted);
            font-size: 15px;
        }

        .band {
            background: var(--soft);
            padding: 86px 0;
        }

        .split {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
        }

        .text-block h2 {
            font-size: clamp(30px, 4vw, 44px);
            color: var(--teal-strong);
        }

        .steps {
            display: grid;
            gap: 14px;
            margin-top: 26px;
        }

        .step {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 16px 18px;
            display: flex;
            gap: 12px;
            align-items: center;
            box-shadow: 0 12px 28px rgba(24, 45, 54, 0.08);
        }

        .step-number {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: var(--accent-dark);
            color: #fff;
            display: grid;
            place-items: center;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .phone-panel {
            background: var(--panel);
            color: #fff;
            border-radius: 28px;
            padding: 30px;
            overflow: hidden;
            position: relative;
        }

        .phone-panel p {
            color: rgba(255, 255, 255, 0.78);
        }

        .phones {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-top: 24px;
        }

        .phone {
            min-height: 280px;
            border: 9px solid #15252c;
            border-radius: 28px;
            background: #f4fbfb;
            padding: 14px 10px;
            box-shadow: 0 16px 28px rgba(0, 0, 0, 0.20);
        }

        .phone-top {
            height: 36px;
            border-radius: 14px;
            background: var(--teal);
            margin-bottom: 12px;
        }

        .phone-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 10px;
            margin-bottom: 10px;
        }

        .line {
            height: 8px;
            border-radius: 999px;
            background: #dceeed;
            margin-bottom: 8px;
        }

        .line.short {
            width: 62%;
        }

        .final-cta {
            padding: 76px 0;
            background: #fff;
            text-align: center;
        }

        .final-cta h2 {
            margin: 0 0 12px;
            font-size: clamp(30px, 5vw, 48px);
            color: var(--dark);
        }

        .final-cta p {
            margin: 0 auto 24px;
            max-width: 620px;
            color: var(--muted);
        }

        .pilot-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 54px;
            padding: 0 32px;
            border-radius: 999px;
            background: var(--teal-strong);
            color: #fff;
            font-weight: 900;
            box-shadow: 0 14px 28px rgba(0, 184, 175, 0.22);
        }

        @media (max-width: 900px) {
            .links {
                display: none;
            }

            .hero {
                min-height: auto;
            }

            .hero::before {
                display: none;
            }

            .hero::after {
                display: none;
            }

            .hero .section {
                min-height: 620px;
                grid-template-columns: 1fr;
                align-items: start;
                padding-top: 74px;
            }

            .hero-visual {
                display: none;
            }

            .service-grid,
            .split {
                grid-template-columns: 1fr;
            }

            .service-grid {
                margin-top: 26px;
            }

            .phones {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 560px) {
            .top-strip .inner,
            .nav,
            .section {
                width: 100%;
                max-width: 100%;
                margin: 0;
                padding-inline: 16px;
            }

            .top-strip .inner {
                align-items: flex-start;
                flex-direction: column;
                padding-block: 10px;
                gap: 6px;
                font-size: 12px;
                overflow-wrap: anywhere;
            }

            .nav {
                min-height: 84px;
                gap: 10px;
            }

            body.site-ar .nav {
                direction: ltr;
            }

            .doctor-cta {
                display: none;
            }

            .brand {
                min-width: 0;
                font-size: 22px;
            }

            .language-switch {
                padding: 7px 12px;
                font-size: 14px;
            }

            .hero .section {
                display: block;
                min-height: auto;
                padding-top: 46px;
                padding-bottom: 34px;
                direction: inherit;
            }

            .hero-copy {
                width: min(100%, 300px);
                max-width: calc(100vw - 64px);
                margin-inline: auto;
                justify-self: stretch;
                overflow-wrap: anywhere;
            }

            body.site-ar .hero-copy {
                direction: rtl;
                text-align: center;
            }

            h1 {
                font-size: 34px;
                max-width: 100%;
            }

            .lead {
                width: 100%;
                max-width: 100%;
                font-size: 16px;
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            .search-box {
                width: 100%;
                max-width: 100%;
                min-height: 62px;
            }

            .service-grid {
                padding-top: 22px;
                justify-items: center;
            }

            .service {
                width: min(100%, 300px);
                max-width: calc(100vw - 64px);
            }

            .top-strip,
            .service h2,
            .service p {
                overflow-wrap: anywhere;
                word-break: break-word;
            }

            .top-strip .inner div {
                width: min(100%, 300px);
                max-width: calc(100vw - 64px);
                margin-inline: auto;
            }

            body.site-ar .top-strip .inner div {
                direction: rtl;
                text-align: center;
            }
        }
    </style>
</head>
<body class="{{ $isEnglish ? 'site-en' : 'site-ar' }}">
    <header>
        <div class="top-strip">
            <div class="inner">
                <div><span class="accent">{{ $copy['siteLabel'] }}:</span> {{ $copy['siteText'] }}</div>
                <div><span class="accent">{{ $copy['supportLabel'] }}:</span> {{ $copy['supportText'] }}</div>
            </div>
        </div>
        <nav class="nav" aria-label="{{ $isEnglish ? 'Main navigation' : 'التنقل الرئيسي' }}">
            <a class="brand" href="{{ url('/') }}">
                <span class="mark">+</span>
                <span>{{ $copy['brand'] }}</span>
            </a>
            <div class="links">
                <a href="#doctors">{{ $copy['navHome'] }}</a>
                <a href="#services">{{ $copy['navAbout'] }}</a>
                <a href="#app">{{ $copy['navSpecialist'] }}</a>
                <a href="#pilot">{{ $copy['navDoctors'] }}</a>
            </div>
            <a class="doctor-cta" href="#pilot">{{ $copy['doctorCta'] }}</a>
            <a class="language-switch" href="{{ $switchUrl }}">{{ $switchLabel }}</a>
        </nav>
    </header>

    <main>
        <section class="hero" id="doctors">
            <div class="section">
                <div class="hero-copy">
                    <h1>{{ $copy['heroTitle'] }}</h1>
                    <p class="lead">{{ $copy['heroLead'] }}</p>
                    <a class="search-box" href="#pilot" aria-label="{{ $copy['searchLabel'] }}">
                        <span>{{ $copy['searchText'] }}</span>
                        <span class="search-button">›</span>
                    </a>
                    <div class="category-kicker">{{ $copy['category'] }}</div>
                </div>
                <div class="hero-visual" aria-hidden="true">
                    <img src="{{ $legacyHeroUrl }}" alt="">
                </div>
            </div>
        </section>

        <section class="section service-grid" id="services" aria-label="{{ $copy['servicesLabel'] }}">
            <article class="service">
                <div class="service-icon">01</div>
                <h2>{{ $copy['serviceDoctor'] }}</h2>
                <p>{{ $copy['serviceDoctorText'] }}</p>
            </article>
            <article class="service">
                <div class="service-icon">02</div>
                <h2>{{ $copy['servicePharmacy'] }}</h2>
                <p>{{ $copy['servicePharmacyText'] }}</p>
            </article>
            <article class="service">
                <div class="service-icon">03</div>
                <h2>{{ $copy['serviceLabs'] }}</h2>
                <p>{{ $copy['serviceLabsText'] }}</p>
            </article>
            <article class="service">
                <div class="service-icon">04</div>
                <h2>{{ $copy['serviceHealth'] }}</h2>
                <p>{{ $copy['serviceHealthText'] }}</p>
            </article>
        </section>

        <section class="band" id="app">
            <div class="section split">
                <div class="text-block">
                    <h2>{{ $copy['flowTitle'] }}</h2>
                    <p>{{ $copy['flowText'] }}</p>
                    <div class="steps">
                        <div class="step"><span class="step-number">1</span><span>{{ $copy['step1'] }}</span></div>
                        <div class="step"><span class="step-number">2</span><span>{{ $copy['step2'] }}</span></div>
                        <div class="step"><span class="step-number">3</span><span>{{ $copy['step3'] }}</span></div>
                    </div>
                </div>
                <div class="phone-panel">
                    <h2>{{ $copy['mockTitle'] }}</h2>
                    <p>{{ $copy['mockText'] }}</p>
                    <div class="phones" aria-hidden="true">
                        <div class="phone">
                            <div class="phone-top"></div>
                            <div class="phone-card"><div class="line"></div><div class="line short"></div></div>
                            <div class="phone-card"><div class="line"></div><div class="line"></div></div>
                            <div class="phone-card"><div class="line short"></div></div>
                        </div>
                        <div class="phone">
                            <div class="phone-top"></div>
                            <div class="phone-card"><div class="line"></div><div class="line short"></div></div>
                            <div class="phone-card"><div class="line"></div><div class="line"></div></div>
                            <div class="phone-card"><div class="line short"></div></div>
                        </div>
                        <div class="phone">
                            <div class="phone-top"></div>
                            <div class="phone-card"><div class="line"></div><div class="line short"></div></div>
                            <div class="phone-card"><div class="line"></div><div class="line"></div></div>
                            <div class="phone-card"><div class="line short"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="final-cta" id="pilot">
            <div class="section">
                <h2>{{ $copy['finalTitle'] }}</h2>
                <p>{{ $copy['finalText'] }}</p>
                <a class="pilot-button" href="mailto:pilot@etamen.local">{{ $copy['pilotButton'] }}</a>
            </div>
        </section>
    </main>
</body>
</html>
