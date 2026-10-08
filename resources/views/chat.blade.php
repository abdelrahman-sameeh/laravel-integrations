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

                <section aria-labelledby="conversations-title">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1" id="conversations-title">المحادثات الأخيرة</h2>
                            <p class="text-secondary small mb-0">كل محادثاتك الفردية في مكان واحد.</p>
                        </div>
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('friends.index') }}">
                            <i class="bi bi-person-plus ms-1"></i>
                            محادثة جديدة
                        </a>
                    </div>

                    @forelse ($conversations as $item)
                        <a class="card border-0 shadow-sm rounded-4 text-decoration-none text-body mb-3"
                           href="{{ route('conversation.show', $item['friend']) }}">
                            <div class="card-body d-flex align-items-center gap-3 p-3 p-lg-4">
                                <span class="friend-avatar bg-primary-subtle text-primary" aria-hidden="true">
                                    <i class="bi bi-person"></i>
                                </span>

                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-1">
                                        <h3 class="h6 fw-bold text-truncate mb-0">{{ $item['friend']->name }}</h3>
                                        <time class="text-secondary flex-shrink-0" style="font-size: .75rem;"
                                              datetime="{{ $item['latestMessage']->created_at->toIso8601String() }}">
                                            {{ $item['latestMessage']->created_at->diffForHumans() }}
                                        </time>
                                    </div>
                                    <p class="text-secondary text-truncate small mb-0">
                                        @if ($item['latestMessage']->sender_id === auth()->id())
                                            <span class="fw-semibold">أنت:</span>
                                        @endif
                                        {{ $item['latestMessage']->content }}
                                    </p>
                                </div>

                                <i class="bi bi-chevron-left text-secondary" aria-hidden="true"></i>
                            </div>
                        </a>
                    @empty
                        <div class="empty-chat-card">
                            <span class="empty-chat-icon"><i class="bi bi-chat-square-text"></i></span>
                            <h2 class="h5 fw-bold">لا توجد محادثات بعد</h2>
                            <p class="text-secondary mb-3">اختر صديقًا وأرسل أول رسالة لتبدأ المحادثة.</p>
                            <a class="btn btn-primary" href="{{ route('friends.index') }}">عرض الأصدقاء</a>
                        </div>
                    @endforelse

                    @if ($conversations->hasPages())
                        <div class="mt-4">
                            {{ $conversations->links() }}
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </div>
</body>
</html>
