<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>اطمن | Find A Doctor</title>
    @php
        $legacyHeroPath = public_path('legacy-doctorfinder/doctor-finder-hero.jpg');
        $legacyHeroUrl = is_file($legacyHeroPath)
            ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($legacyHeroPath))
            : asset('legacy-doctorfinder/doctor-finder-hero.jpg');
    @endphp
    <style>
        :root {
            --teal: #01d8c9;
            --teal-strong: #00b8af;
            --dark: #17242b;
            --panel: #203b46;
            --muted: #66737b;
            --orange: #ff8f2c;
            --mint: #e6fffc;
            --soft: #f7fafa;
            --border: #d7f4f0;
        }

        * {
            box-sizing: border-box;
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
            color: var(--orange);
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
            gap: 30px;
            color: #52606a;
            font-weight: 700;
        }

        .doctor-cta {
            background: var(--orange);
            color: #fff;
            padding: 14px 26px;
            border-radius: 999px;
            font-weight: 900;
            box-shadow: 0 14px 26px rgba(255, 143, 44, 0.24);
        }

        .hero {
            min-height: 650px;
            background: #ffe9d2;
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

        .search-button {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: var(--orange);
            color: #fff;
            display: grid;
            place-items: center;
            font-size: 26px;
            font-weight: 900;
        }

        .category-kicker {
            margin-top: 44px;
            color: var(--orange);
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
            background: linear-gradient(90deg, rgba(255, 233, 210, 0.04), rgba(1, 216, 201, 0.10));
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
            background: var(--orange);
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
            .top-strip .inner {
                align-items: flex-start;
                flex-direction: column;
                padding: 10px 0;
            }

            .nav {
                min-height: 84px;
            }

            .doctor-cta {
                display: none;
            }

            .hero .section {
                min-height: 570px;
                padding-top: 52px;
            }

            .search-box {
                min-height: 62px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="top-strip">
            <div class="inner">
                <div><span class="accent">الموقع:</span> خدمات اطمن التجريبية للمرضى</div>
                <div><span class="accent">الدعم:</span> تفعيل تجريبي تحت المراجعة</div>
            </div>
        </div>
        <nav class="nav" aria-label="التنقل الرئيسي">
            <a class="brand" href="{{ url('/') }}">
                <span class="mark">+</span>
                <span>اطمن</span>
            </a>
            <div class="links">
                <a href="#doctors">Home</a>
                <a href="#services">About Us</a>
                <a href="#app">Specialist</a>
                <a href="#pilot">Doctors</a>
            </div>
            <a class="doctor-cta" href="#pilot">+ Join As Doctor</a>
        </nav>
    </header>

    <main>
        <section class="hero" id="doctors">
            <div class="section">
                <div class="hero-copy">
                    <h1>Find A Doctor!</h1>
                    <p class="lead">واجهة اطمن الجديدة تعيد تجربة Doctor Finder القديمة: بحث واضح، ألوان طبية، وحجز بسيط من أول شاشة.</p>
                    <a class="search-box" href="#pilot" aria-label="ابدأ البحث عن طبيب">
                        <span>Ex. Doctor Name</span>
                        <span class="search-button">›</span>
                    </a>
                    <div class="category-kicker">CATEGORY</div>
                </div>
                <div class="hero-visual" aria-hidden="true">
                    <img src="{{ $legacyHeroUrl }}" alt="">
                </div>
            </div>
        </section>

        <section class="section service-grid" id="services" aria-label="خدمات اطمن">
            <article class="service">
                <div class="service-icon">01</div>
                <h2>Find a doctor</h2>
                <p>بحث بالتخصص، بروفايل واضح، واختيار موعد بدون تعقيد.</p>
            </article>
            <article class="service">
                <div class="service-icon">02</div>
                <h2>Pharmacy orders</h2>
                <p>طلبات أدوية وروشتات مع تجربة كروت بيضاء قريبة من القديم.</p>
            </article>
            <article class="service">
                <div class="service-icon">03</div>
                <h2>Lab tests</h2>
                <p>اختيار معامل وتحاليل مع حالة طلب مفهومة للمريض.</p>
            </article>
            <article class="service">
                <div class="service-icon">04</div>
                <h2>Health tracking</h2>
                <p>متابعة قياسات وتنبيهات بدون شكل إداري ثقيل.</p>
            </article>
        </section>

        <section class="band" id="app">
            <div class="section split">
                <div class="text-block">
                    <h2>تجربة حجز أقرب للتطبيق القديم</h2>
                    <p>التركيز هنا ليس على إضافة مزايا جديدة، بل على استرجاع جودة الواجهة القديمة: هيدر طبي، كروت أطباء بصورة أو placeholder، وخطوات حجز قصيرة.</p>
                    <div class="steps">
                        <div class="step"><span class="step-number">1</span><span>ابحث باسم الطبيب أو التخصص.</span></div>
                        <div class="step"><span class="step-number">2</span><span>راجع البروفايل والسعر والمكان.</span></div>
                        <div class="step"><span class="step-number">3</span><span>اختر الموعد ثم ارفع إثبات الدفع عند الحاجة.</span></div>
                    </div>
                </div>
                <div class="phone-panel">
                    <h2>Mock app screens</h2>
                    <p>كروت هاتف توضح الاتجاه البصري الجديد بدون نسخ screenshots داخل المنتج.</p>
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
                <h2>جاهز لمراجعة المنتج بصريًا</h2>
                <p>هذه صفحة تعريفية خفيفة للمراجعة التجريبية وليست CMS أو بوابة دفع أو تسجيل ويب. الإطلاق العام يحتاج محتوى وتسويق وصور نهائية مرخصة.</p>
                <a class="pilot-button" href="mailto:pilot@etamen.local">طلب مراجعة Pilot</a>
            </div>
        </section>
    </main>
</body>
</html>
