@extends('layouts.app')

@section('title', 'إتمام الطلب')

@section('content')
    <h1 class="mb-4 text-xl font-bold">إتمام الطلب</h1>

    @if (count($methods) === 0)
        <p class="card p-6 text-center text-stone-700">لا توجد طريقة دفع مفعّلة حاليًا. يرجى التواصل مع المتجر.</p>
    @else
        <form action="{{ route('checkout.store') }}" method="post" class="grid gap-5 md:grid-cols-3" data-once data-checkout data-subtotal="{{ $subtotal }}" data-base-shipping="{{ $baseShipping }}" data-symbol="{{ $store->currency_symbol }}">
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $token }}">
            {{-- مصيدة للروبوتات: لا يراها الزبون الحقيقي --}}
            <div class="hidden" aria-hidden="true">
                <label>لا تملأ هذا الحقل <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="space-y-4 md:col-span-2">
                @guest
                    <p class="card p-3 text-sm text-stone-700">عندك حساب؟ <a class="font-semibold text-primary underline" href="{{ route('login') }}">سجّل الدخول</a> لتتابع طلباتك، أو أكمل كضيف.</p>
                @endguest

                <section class="card space-y-4 p-4">
                    <h2 class="font-bold">بيانات الزبون والعنوان</h2>
                    <div>
                        <label for="name" class="label">الاسم الكامل *</label>
                        <input id="name" name="name" value="{{ old('name', $user?->name) }}" required maxlength="120" autocomplete="name" class="input">
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="label">رقم الهاتف *</label>
                            <input id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel" dir="ltr" autocomplete="tel" class="input text-start">
                        </div>
                        <div>
                            <label for="email" class="label">البريد الإلكتروني (اختياري)</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" dir="ltr" autocomplete="email" class="input text-start">
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="city" class="label">المدينة *</label>
                            <input id="city" name="city" value="{{ old('city') }}" required maxlength="100" autocomplete="address-level2" class="input">
                        </div>
                        <div>
                            <label for="address" class="label">العنوان بالتفصيل *</label>
                            <input id="address" name="address" value="{{ old('address') }}" required maxlength="255" autocomplete="street-address" class="input">
                        </div>
                    </div>
                    @if ($zones->isNotEmpty())
                        <div>
                            <label for="shipping_zone_id" class="label">منطقة التوصيل *</label>
                            <select id="shipping_zone_id" name="shipping_zone_id" required class="input" data-zone>
                                @foreach ($zones as $zone)
                                    <option value="{{ $zone->id }}" data-fee="{{ $zone->fee }}" @selected((int) old('shipping_zone_id', $zones->first()->id) === $zone->id)>{{ $zone->name }} — {{ (float) $zone->fee > 0 ? $store->money($zone->fee) : 'مجاني' }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label for="notes" class="label">ملاحظات على الطلب (اختياري)</label>
                        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="input">{{ old('notes') }}</textarea>
                    </div>
                </section>

                <section class="card space-y-3 p-4">
                    <h2 class="font-bold">طريقة الدفع</h2>
                    @foreach ($methods as $key => $method)
                        <label class="flex min-h-12 cursor-pointer items-start gap-3 rounded-xl border border-stone-200 p-3 has-[:checked]:border-primary has-[:checked]:bg-secondary/30">
                            <input type="radio" name="payment_method" value="{{ $key }}" class="mt-1 size-5" @checked(old('payment_method', array_key_first($methods)) === $key) required>
                            <span>
                                <span class="block font-semibold">{{ $method->label($settings) }}</span>
                                @if ($method->description($settings))
                                    <span class="block whitespace-pre-line text-sm text-stone-700">{{ $method->description($settings) }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </section>
            </div>

            <aside class="card h-fit space-y-3 p-4" aria-label="ملخص الطلب">
                <h2 class="font-bold">ملخص الطلب</h2>
                <ul class="space-y-1.5 text-sm text-stone-700">
                    @foreach ($lines as $line)
                        <li class="flex justify-between gap-2">
                            <span>{{ $line['product']->name }}@if($line['variant']) ({{ $line['variant']->label }})@endif × <bdi>{{ $line['quantity'] }}</bdi></span>
                            <bdi class="shrink-0">{{ $store->money($line['line_total']) }}</bdi>
                        </li>
                    @endforeach
                </ul>
                @php($firstFee = $zones->isNotEmpty() ? (float) $zones->first()->fee : $baseShipping)
                <dl class="space-y-2 border-t border-stone-200 pt-3 text-sm">
                    <div class="flex justify-between"><dt>المجموع الفرعي</dt><dd><bdi>{{ $store->money($subtotal) }}</bdi></dd></div>
                    <div class="flex justify-between"><dt>الشحن</dt><dd data-shipping><bdi>{{ $firstFee > 0 ? $store->money($firstFee) : 'مجاني' }}</bdi></dd></div>
                    <div class="flex justify-between text-lg font-bold"><dt>الإجمالي</dt><dd data-total><bdi>{{ $store->money($subtotal + $firstFee) }}</bdi></dd></div>
                </dl>
                <p class="text-xs text-stone-600">السعر النهائي يُحسب على الخادم عند تأكيد الطلب.</p>
                <button type="submit" class="btn btn-primary btn-lg w-full" data-submit-label="جارٍ تأكيد الطلب…">تأكيد الطلب</button>
            </aside>
        </form>
    @endif
@endsection
