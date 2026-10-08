@extends('layouts.auth')

@section('title', 'إنشاء حساب')

@section('content')
    <div class="auth-card">
        <span class="auth-eyebrow"><i class="bi bi-stars"></i> ابدأ من هنا</span>
        <h2>أنشئ حسابك</h2>
        <p class="auth-subtitle">خطوة واحدة تفصلك عن محادثات أسرع وأكثر وضوحًا.</p>

        <form
            action="{{ route('register.store') }}"
            method="post"
        >
            @csrf

            <div class="form-field mb-3">
                <label class="form-label" for="name">الاسم</label>
                <div class="input-group auth-input">
                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                    <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="اكتب اسمك الكامل" autocomplete="name" required autofocus>
                </div>
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-field mb-3">
                <label class="form-label" for="email">البريد الإلكتروني</label>
                <div class="input-group auth-input">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@example.com" autocomplete="email" required>
                </div>
                @error('email')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3">
                <div class="form-field col-sm-6">
                    <label class="form-label" for="password">كلمة المرور</label>
                    <div class="input-group auth-input">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" placeholder="6 أحرف أو أكثر" autocomplete="new-password" required>
                        <button class="btn" type="button" data-password-toggle="password" aria-label="إظهار كلمة المرور">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-field col-sm-6">
                    <label class="form-label" for="password_confirmation">تأكيد كلمة المرور</label>
                    <div class="input-group auth-input">
                        <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                        <input class="form-control" id="password_confirmation" name="password_confirmation" type="password" placeholder="أعد كتابة الكلمة" autocomplete="new-password" required>
                        <button class="btn" type="button" data-password-toggle="password_confirmation" aria-label="إظهار كلمة المرور">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="form-check mt-3">
                <input class="form-check-input" id="terms" name="terms" type="checkbox" value="1" required>
                <label class="form-check-label legal-copy" for="terms">أوافق على شروط الاستخدام وسياسة الخصوصية الخاصة بوصلة.</label>
            </div>

            <button class="btn btn-auth w-100" type="submit">
                <span>إنشاء الحساب</span>
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
            </button>
        </form>

        <p class="auth-switch">لديك حساب بالفعل؟ <a class="auth-link" href="{{ route('login') }}">سجّل الدخول</a></p>
    </div>
@endsection
