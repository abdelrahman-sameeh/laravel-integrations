@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="auth-card">
        <span class="auth-eyebrow"><i class="bi bi-lightning-charge-fill"></i> عُد إلى محادثاتك</span>
        <h2>أهلًا بعودتك</h2>
        <p class="auth-subtitle">سجّل دخولك، وكل ما فاتك سيكون في انتظارك.</p>

        <form
            action="{{ route('login.store') }}"
            method="post"
        >
            @csrf

            @if (session('status'))
                <div class="alert alert-success mt-0 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill ms-1" aria-hidden="true"></i>
                    {{ session('status') }}
                </div>
            @endif

            <div class="form-field mb-3">
                <label class="form-label" for="email">البريد الإلكتروني</label>
                <div class="input-group auth-input">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" autocomplete="email" required autofocus>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field mb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <label class="form-label" for="password">كلمة المرور</label>
                    <a class="auth-link small mb-2" href="#">نسيت كلمة المرور؟</a>
                </div>
                <div class="input-group auth-input">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="أدخل كلمة المرور" autocomplete="current-password" required>
                    <button class="btn" type="button" data-password-toggle="password" aria-label="إظهار كلمة المرور">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
                @error('password')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
                <label class="form-check-label small" for="remember">تذكّرني على هذا الجهاز</label>
            </div>

            <button class="btn btn-auth w-100" type="submit">
                <span>تسجيل الدخول</span>
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </button>
        </form>

        <p class="auth-switch">جديد في وصلة؟ <a class="auth-link" href="{{ route('register') }}">أنشئ حسابك</a></p>
    </div>
@endsection
