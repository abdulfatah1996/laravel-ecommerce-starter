@extends('layouts.app')
@section('title', 'تسجيل الدخول')
@section('content')
    <div class="card mx-auto max-w-md space-y-4 p-6">
        <h1 class="text-xl font-bold">تسجيل الدخول</h1>
        <form action="{{ route('account.login') }}" method="post" class="space-y-4">
            @csrf
            <div><label for="email" class="label">البريد الإلكتروني</label><input id="email" type="email" name="email" value="{{ old('email') }}" required dir="ltr" autocomplete="email" class="input text-start"></div>
            <div><label for="password" class="label">كلمة المرور</label><input id="password" type="password" name="password" required autocomplete="current-password" class="input" dir="ltr"></div>
            <label class="flex min-h-11 items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" class="size-5"> تذكّرني</label>
            <button type="submit" class="btn btn-primary btn-lg w-full">دخول</button>
        </form>
        <p class="text-center text-sm">ليس لديك حساب؟ <a href="{{ route('account.register') }}" class="font-semibold text-primary underline">أنشئ حسابًا</a></p>
    </div>
@endsection
