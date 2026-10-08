<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1324">
    <title>المحادثات | وصلة</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="chat-page">
    <div class="container-fluid">
        <div class="row">
            @include('partials.app-sidebar')

            <main class="app-main col-md-9 col-xl-10">
                <header class="d-flex align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <p class="text-secondary small mb-1">مساحة المحادثات</p>
                        <h1 class="h4 fw-bold mb-0">أهلًا، {{ auth()->user()->name }}</h1>
                    </div>
                    <div class="text-start d-none d-sm-block">
                        <strong class="d-block small">{{ auth()->user()->name }}</strong>
                        <span class="text-secondary small">{{ auth()->user()->email }}</span>
                    </div>
                </header>

                @if (session('success'))
                    <div class="alert alert-success mt-0 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill ms-1" aria-hidden="true"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <section class="welcome-card mb-4">
                    <div class="position-relative z-1">
                        <span class="badge rounded-pill text-bg-light text-primary mb-3">متصل الآن</span>
                        <h2 class="h3 fw-bold">محادثاتك أصبحت أقرب.</h2>
                        <p class="mb-0 text-white-50">ابدأ محادثة جديدة وتواصل في الوقت الحقيقي مع فريقك.</p>
                    </div>
                </section>

                <section class="empty-chat-card">
                    <span class="empty-chat-icon"><i class="bi bi-chat-square-text"></i></span>
                    <h2 class="h5 fw-bold">لا توجد محادثة محددة</h2>
                    <p class="text-secondary mb-0">اختر محادثة أو ابدأ واحدة جديدة لعرض الرسائل هنا.</p>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
