<!doctype html>
<html lang="ar" dir="rtl">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#0b1324">
  <title>{{ $friend->name ?? 'المحادثة' }} </title>
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="chat-page">
  <div class="container-fluid">
    <div class="row">
      @include('partials.app-sidebar')

      <main class="app-main col-md-9 col-xl-10 d-flex flex-column vh-100 p-0">
        <header class="d-flex align-items-center gap-3 px-3 px-lg-4 py-3 bg-white border-bottom">
          <a class="btn btn-light rounded-circle" href="{{ route('chat') }}" aria-label="العودة إلى المحادثات">
            <i class="bi bi-arrow-right"></i>
          </a>

          <span class="friend-avatar bg-primary-subtle text-primary" aria-hidden="true">
            <i class="bi bi-person"></i>
          </span>

          <div class="flex-grow-1 min-w-0">
            <h1 class="h6 fw-bold text-truncate mb-1">{{ $friend->name ?? 'محادثة جديدة' }}</h1>
            <p class="text-secondary small mb-0">محادثة خاصة</p>
          </div>

          <button class="btn btn-light rounded-circle" type="button" aria-label="معلومات المحادثة">
            <i class="bi bi-info-lg"></i>
          </button>
        </header>

        <section class="flex-grow-1 overflow-auto px-3 px-lg-4 py-4" aria-label="رسائل المحادثة">
          <div class="mx-auto" style="max-width: 920px;">
            <div class="d-flex align-items-center gap-3 mb-4" aria-hidden="true">
              <span class="border-top flex-grow-1"></span>
              <span class="badge rounded-pill bg-white border text-secondary px-3 py-2">اليوم</span>
              <span class="border-top flex-grow-1"></span>
            </div>

            @if ($messages instanceof \Illuminate\Contracts\Pagination\Paginator && $messages->hasPages())
              <div class="mb-4">
                {{ $messages->links() }}
              </div>
            @endif

            @forelse (($messages ?? collect()) as $message)
            @php($isOwnMessage = $message->sender_id === auth()->id())

            <article class="d-flex {{ $isOwnMessage ? 'justify-content-start' : 'justify-content-end' }} mb-3">
              <div class="d-flex align-items-end gap-2 {{ $isOwnMessage ? '' : 'flex-row-reverse' }}"
                style="max-width: min(78%, 620px);">
                @unless ($isOwnMessage)
                  <span class="friend-avatar bg-white border text-secondary flex-shrink-0" aria-hidden="true"
                    style="width: 36px; height: 36px; flex-basis: 36px; border-radius: 12px;">
                    <i class="bi bi-person"></i>
                  </span>
                @endunless

                <div>
                  <div class="{{ $isOwnMessage ? 'bg-primary text-white' : 'bg-white border' }} px-3 py-2 shadow-sm"
                    style="border-radius: 18px 18px {{ $isOwnMessage ? '4px 18px' : '18px 4px' }};">
                    <p class="mb-1 lh-lg">{{ $message->content }}</p>
                    <div
                      class="d-flex align-items-center justify-content-end gap-1 {{ $isOwnMessage ? 'text-white-50' : 'text-secondary' }}"
                      style="font-size: .7rem;">
                      <time datetime="{{ $message->created_at?->toIso8601String() }}">
                        {{ $message->created_at?->format('h:i A') }}
                      </time>
                      @if ($isOwnMessage)
                        <i class="bi bi-check2" aria-label="تم الإرسال"></i>
                      @endif
                    </div>
                  </div>
                </div>
              </div>
            </article>
            @empty
            <div class="d-flex flex-column align-items-center justify-content-center text-center py-5 my-5">
              <span class="empty-chat-icon">
                <i class="bi bi-chat-heart"></i>
              </span>
              <h2 class="h5 fw-bold">ابدأ المحادثة</h2>
              <p class="text-secondary mb-0">أرسل أول رسالة وافتح باب التواصل.</p>
            </div>
            @endforelse
          </div>
        </section>

        <footer class="bg-white border-top px-3 px-lg-4 py-3">
          <div class="mx-auto" style="max-width: 920px;">
            @error('content')
              <div class="alert alert-danger py-2 mb-3" role="alert">{{ $message }}</div>
            @enderror

            <form action="{{ $sendMessageUrl ?? '#' }}" method="post" class="d-flex align-items-end gap-2">
              @csrf
              <div class="flex-grow-1">
                <label class="visually-hidden" for="message-content">نص الرسالة</label>
                <textarea class="form-control border-0 bg-light rounded-4 px-3 py-2" id="message-content" name="content"
                  rows="1" maxlength="2000" placeholder="اكتب رسالة..." required>{{ old('content') }}</textarea>
              </div>

              <button class="btn btn-primary rounded-circle flex-shrink-0" type="submit" aria-label="إرسال الرسالة">
                <i class="bi bi-send"></i>
              </button>
            </form>
          </div>
        </footer>
      </main>
    </div>
  </div>
</body>

</html>
