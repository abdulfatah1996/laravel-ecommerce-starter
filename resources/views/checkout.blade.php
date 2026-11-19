@extends('layouts.app')

@section('title', 'إتمام الطلب')

@section('content')
    <h1 class="mb-4 text-xl font-bold">إتمام الطلب</h1>

    @if (count($methods) === 0)
        <p class="card p-6 text-center text-stone-600">لا توجد طريقة دفع مفعّلة حاليًا. يرجى التواصل مع المتجر.</p>
    @else
        <form action="{{ route('checkout.store') }}" method="post" class="grid gap-6 md:grid-cols-3" data-once>
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $token }}">
            {{-- مصيدة للروبوتات: لا يراها الزبون الحقيقي --}}
            <div class="hidden" aria-hidden="true">
                <label>لا تملأ هذا الحقل <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="space-y-4 md:col-span-2">
                <section class="card space-y-4 p-4">
                    <h2 class="font-bold">بيانات الزبون والعنوان</h2>
                    <div>
                        <label for="name" class="label">الاسم الكامل *</label>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name" class="input">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="label">رقم الهاتف *</label>
                            <input id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel" dir="ltr" autocomplete="tel" class="input text-start">
                        </div>
                        <div>
                            <label for="email" class="label">البريد الإلكتروني (اختياري)</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" dir="ltr" autocomplete="email" class="input text-start">
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="city" class="label">المدينة *</label>
                            <input id="city" name="city" value="{{ old('city') }}" required maxlength="100" class="input">
                        </div>
                        <div>
                            <label for="address" class="label">العنوان بالتفصيل *</label>
                            <input id="address" name="address" value="{{ old('address') }}" required maxlength="255" class="input">
                        </div>
                    </div>
                    <div>
                        <label for="notes" class="label">ملاحظات على الطلب (اختياري)</label>
                        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="input">{{ old('notes') }}</textarea>
                    </div>
                </section>

                <section class="card space-y-3 p-4">
                    <h2 class="font-bold">طريقة الدفع</h2>
                    @foreach ($methods as $key => $method)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-stone-200 p-3 has-[:checked]:border-primary has-[:checked]:bg-secondary/30">
                            <input type="radio" name="payment_method" value="{{ $key }}" class="mt-1" @checked(old('payment_method', array_key_first($methods)) === $key) required>
                            <span>
                                <span class="block font-semibold">{{ $method->label($settings) }}</span>
                                @if ($method->description($settings))
                                    <span class="block whitespace-pre-line text-sm text-stone-600">{{ $method->description($settings) }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </section>
            </div>

            <aside class="card h-fit space-y-3 p-4" aria-label="ملخص الطلب">
                <h2 class="font-bold">ملخص الطلب</h2>
                <ul class="space-y-1 text-sm text-stone-600">
                    @foreach ($lines as $line)
                        <li class="flex justify-between gap-2">
                            <span>{{ $line['product']->name }} × {{ $line['quantity'] }}</span>
                            <span>{{ $store->money($line['line_total']) }}</span>
                        </li>
                    @endforeach
                </ul>
                <dl class="space-y-2 border-t border-stone-200 pt-3 text-sm">
                    <div class="flex justify-between"><dt>المجموع الفرعي</dt><dd>{{ $store->money($subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt>الشحن</dt><dd>{{ $shipping > 0 ? $store->money($shipping) : 'مجاني' }}</dd></div>
                    <div class="flex justify-between text-base font-bold"><dt>الإجمالي</dt><dd>{{ $store->money($total) }}</dd></div>
                </dl>
                <p class="text-xs text-stone-500">السعر النهائي يُحسب على الخادم عند تأكيد الطلب.</p>
                <button type="submit" class="btn btn-primary w-full">تأكيد الطلب</button>
            </aside>
        </form>
    @endif
@endsection
