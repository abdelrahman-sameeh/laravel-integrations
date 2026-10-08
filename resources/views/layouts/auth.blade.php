<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1324">
    <meta name="description" content="وصلة — مساحة آمنة وسريعة لمحادثاتك الفورية.">
    <title>@yield('title') | وصلة</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-page">
    <main class="auth-shell container-fluid px-0">
        <div class="row g-0 min-vh-100">
            <aside class="brand-panel col-lg-5" aria-label="عن وصلة">
                <div class="brand-content">
                    <a class="brand-link" href="{{ route('login') }}" aria-label="وصلة، الصفحة الرئيسية">
                        <span class="brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M9 8.5L22.5 16L9 23.5" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <circle cx="8" cy="8" r="3" fill="currentColor"/>
                                <circle cx="24" cy="16" r="3" fill="currentColor"/>
                                <circle cx="8" cy="24" r="3" fill="currentColor"/>
                            </svg>
                        </span>
                        <span class="brand-name">وصلة</span>
                    </a>

                    <div class="brand-copy">
                        <p class="brand-kicker">تواصل لحظي. حضور حقيقي.</p>
                        <h1>المسافة أقصر<br>حين تصل الرسالة.</h1>
                        <p>مساحة واحدة تجمع محادثاتك وتُبقي فريقك على نفس الموجة، بسرعة وأمان ومن دون ضوضاء.</p>

                        <div class="connection-visual" aria-hidden="true">
                            <span class="connection-line"></span>
                            <span class="avatar-node">ن</span>
                            <span class="message-pill">تم التسليم <i class="bi bi-check2-all"></i></span>
                            <span class="avatar-node">م</span>
                        </div>
                    </div>

                    <div class="brand-note">
                        <span class="status-dot" aria-hidden="true"></span>
                        <span>اتصال مشفّر ومستقر على مدار الساعة</span>
                    </div>
                </div>
            </aside>

            <section class="form-panel col-lg-7">
                @yield('content')
            </section>
        </div>
    </main>
</body>
</html>
