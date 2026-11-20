@extends('layouts.app')
@section('title', 'حساب جديد')
@section('content')
    <div class="card mx-auto max-w-md space-y-4 p-6">
        <h1 class="text-xl font-bold">إنشاء حساب</h1>
        <form action="{{ route('account.register.store') }}" method="post" class="space-y-4">
            @csrf
            <div><label for="name" class="label">الاسم</label><input id="name" name="name" value="{{ old('name') }}" required autocomplete="name" class="input"></div>
            <div><label for="email" class="label">البريد الإلكتروني</label><input id="email" type="email" name="email" value="{{ old('email') }}" required dir="ltr" autocomplete="email" class="input text-start"></div>
            <div><label for="password" class="label">كلمة المرور (8 أحرف فأكثر)</label><input id="password" type="password" name="password" required minlength="8" autocomplete="new-password" class="input" dir="ltr"></div>
            <div><label for="password_confirmation" class="label">تأكيد كلمة المرور</label><input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="input" dir="ltr"></div>
            <button type="submit" class="btn btn-primary btn-lg w-full">إنشاء الحساب</button>
        </form>
        <p class="text-center text-sm">عندك حساب؟ <a href="{{ route('login') }}" class="font-semibold text-primary underline">سجّل الدخول</a></p>
    </div>
@endsection
