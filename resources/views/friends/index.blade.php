<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b1324">
    <title>الأصدقاء | وصلة</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="chat-page">
    <div class="container-fluid">
        <div class="row">
            @include('partials.app-sidebar')

            <main class="app-main col-md-9 col-xl-10">
                <header class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <p class="text-secondary small mb-1">شبكتك في وصلة</p>
                        <h1 class="h3 fw-bold mb-0">الأصدقاء</h1>
                    </div>
                    @if ($incomingRequests->isNotEmpty())
                        <span class="badge rounded-pill text-bg-primary px-3 py-2">
                            {{ $incomingRequests->count() }} طلب جديد
                        </span>
                    @endif
                </header>

                @if (session('success'))
                    <div class="alert alert-success mt-0 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill ms-1" aria-hidden="true"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @error('friend')
                    <div class="alert alert-danger mt-0 mb-4" role="alert">
                        <i class="bi bi-exclamation-circle-fill ms-1" aria-hidden="true"></i>
                        {{ $message }}
                    </div>
                @enderror

                @if ($incomingRequests->isNotEmpty())
                    <section class="mb-5" aria-labelledby="incoming-title">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h2 class="h5 fw-bold mb-0" id="incoming-title">طلبات واردة</h2>
                            <span class="text-secondary small">في انتظار ردك</span>
                        </div>
                        <div class="row g-3">
                            @foreach ($incomingRequests as $item)
                                <div class="col-xl-6">
                                    <article class="card border-0 shadow-sm h-100 rounded-4">
                                        <div class="card-body d-flex flex-wrap align-items-center gap-3 p-4">
                                            <span class="friend-avatar bg-primary-subtle text-primary" aria-hidden="true">
                                                <i class="bi bi-person"></i>
                                            </span>
                                            <div class="flex-grow-1">
                                                <h3 class="h6 fw-bold mb-1">{{ $item['user']->name }}</h3>
                                                <p class="text-secondary small mb-0">{{ $item['user']->email }}</p>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <form action="{{ route('friend-requests.accept', $item['friendship']) }}" method="post">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button class="btn btn-primary btn-sm px-3" type="submit">قبول</button>
                                                </form>
                                                <form action="{{ route('friend-requests.destroy', $item['friendship']) }}" method="post">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-outline-secondary btn-sm px-3" type="submit">رفض</button>
                                                </form>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section class="mb-5" aria-labelledby="friends-title">
                    <h2 class="h5 fw-bold mb-3" id="friends-title">أصدقائي</h2>
                    @if ($friends->isEmpty())
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4 text-secondary">
                                <i class="bi bi-people ms-2" aria-hidden="true"></i>
                                لم تضف أصدقاء بعد. ابحث عن شخص في الأسف وأرسل له طلبًا.
                            </div>
                        </div>
                    @else
                        <div class="row g-3">
                            @foreach ($friends as $item)
                                <div class="col-sm-6 col-xl-4">
                                    <article class="card border-0 shadow-sm h-100 rounded-4">
                                        <div class="card-body d-flex align-items-center gap-3 p-4">
                                            <span class="friend-avatar bg-success-subtle text-success" aria-hidden="true">
                                                <i class="bi bi-person-check"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <h3 class="h6 fw-bold mb-1">{{ $item['user']->name }}</h3>
                                                <p class="text-secondary small text-truncate mb-0">{{ $item['user']->email }}</p>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if ($outgoingRequests->isNotEmpty())
                    <section class="mb-5" aria-labelledby="outgoing-title">
                        <h2 class="h5 fw-bold mb-3" id="outgoing-title">طلباتي المرسلة</h2>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach ($outgoingRequests as $item)
                                <span class="badge rounded-pill bg-white border text-secondary px-3 py-2">
                                    <i class="bi bi-clock ms-1"></i>
                                    {{ $item['user']->name }} · في الانتظار
                                </span>
                            @endforeach
                        </div>
                    </section>
                @endif

                <section aria-labelledby="discover-title">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 fw-bold mb-1" id="discover-title">ابحث عن أصدقاء</h2>
                            <p class="text-secondary small mb-0">ابحث بالاسم أو البريد الإلكتروني.</p>
                        </div>
                        <form action="{{ route('friends.index') }}" method="get" class="d-flex gap-2 friends-search" role="search">
                            <label class="visually-hidden" for="friends-search">البحث عن صديق</label>
                            <input class="form-control" id="friends-search" name="search" type="search" value="{{ $search }}" placeholder="الاسم أو البريد">
                            <button class="btn btn-dark" type="submit"><i class="bi bi-search"></i></button>
                        </form>
                    </div>

                    <div class="row g-3">
                        @forelse ($suggestedUsers as $candidate)
                            <div class="col-sm-6 col-xl-4">
                                <article class="card border-0 shadow-sm h-100 rounded-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center gap-3 mb-3">
                                            <span class="friend-avatar bg-info-subtle text-info-emphasis" aria-hidden="true">
                                                <i class="bi bi-person-plus"></i>
                                            </span>
                                            <div class="min-w-0">
                                                <h3 class="h6 fw-bold mb-1">{{ $candidate->name }}</h3>
                                                <p class="text-secondary small text-truncate mb-0">{{ $candidate->email }}</p>
                                            </div>
                                        </div>
                                        <form action="{{ route('friends.store', $candidate) }}" method="post">
                                            @csrf
                                            <button class="btn btn-outline-primary w-100" type="submit">
                                                <i class="bi bi-person-plus ms-1"></i>
                                                إرسال طلب صداقة
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card border-0 shadow-sm rounded-4">
                                    <div class="card-body p-4 text-center text-secondary">
                                        لا يوجد مستخدمون متاحون يطابقون بحثك.
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    @if ($suggestedUsers->hasPages())
                        <div class="mt-4">
                            {{ $suggestedUsers->links() }}
                        </div>
                    @endif
                </section>
            </main>
        </div>
    </div>
</body>
</html>
