<aside class="app-sidebar col-md-3 col-xl-2 px-0">
    <div class="d-flex align-items-center justify-content-between">
        <a class="brand-link" href="{{ route('chat') }}">
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
        <form action="{{ route('logout') }}" method="post" class="d-md-none ms-3">
            @csrf
            <button class="btn btn-sm text-white" type="submit" aria-label="تسجيل الخروج">
                <i class="bi bi-box-arrow-left"></i>
            </button>
        </form>
    </div>

    <nav class="mt-md-4 pb-3" aria-label="القائمة الرئيسية">
        <a class="app-nav-link {{ request()->routeIs('chat') ? 'active' : '' }}" href="{{ route('chat') }}">
            <i class="bi bi-chat-dots"></i>
            <span>المحادثات</span>
        </a>
        <a class="app-nav-link {{ request()->routeIs('friends.*', 'friend-requests.*') ? 'active' : '' }}" href="{{ route('friends.index') }}">
            <i class="bi bi-people"></i>
            <span>الأصدقاء</span>
        </a>
        <form action="{{ route('logout') }}" method="post" class="d-none d-md-block">
            @csrf
            <button class="app-nav-link border-0 w-auto bg-transparent" type="submit">
                <i class="bi bi-box-arrow-left"></i>
                <span>تسجيل الخروج</span>
            </button>
        </form>
    </nav>
</aside>
