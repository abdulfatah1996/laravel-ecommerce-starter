@extends('layouts.app')

@section('title', 'إتمام الطلب')
@section('inline_errors', '1')
@section('body_class', 'pb-28 md:pb-0')

@section('content')
    <h1 class="t-title mb-5">إتمام الطلب</h1>

    @if (count($methods) === 0)
        <x-empty-state icon="alert" title="لا توجد طريقة دفع مفعّلة حاليًا" text="يرجى التواصل مع المتجر لإتمام طلبك." />
    @else
        <form action="{{ route('checkout.store') }}" method="post" class="grid gap-6 md:grid-cols-[1fr_24rem] md:items-start md:gap-8" data-once id="checkout-form">
            @csrf
            <input type="hidden" name="checkout_token" value="{{ $token }}">
            {{-- مصيدة للروبوتات: لا يراها الزبون الحقيقي --}}
            <div class="hidden" aria-hidden="true">
                <label>لا تملأ هذا الحقل <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            {{-- ملخص قابل للطي على الهاتف (يظهر أولًا ليعرف الزبون ما يشتريه) --}}
            <details class="fold card md:hidden">
                <summary class="px-4">
                    <span>ملخص الطلب <span class="t-small t-muted font-medium">(<bdi dir="ltr">{{ count($lines) }}</bdi>)</span></span>
                    <span class="flex items-center gap-2"><x-price :amount="$total" size="md" /><x-icon name="chevron-down" class="fold-icon size-5" /></span>
                </summary>
                <div class="border-t px-4 pb-4 pt-4" style="border-color: var(--line)">@include('partials.order-summary')</div>
            </details>

            <div class="min-w-0 space-y-5 md:order-first">
                <section class="card card-pad space-y-4" aria-labelledby="sec-customer">
                    <h2 id="sec-customer" class="t-heading">بيانات الزبون والعنوان</h2>
                    <x-field name="name" label="الاسم الكامل" required>
                        <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" autocomplete="name" class="input" @if ($errors->has('name')) aria-invalid="true" aria-describedby="name-error" @endif>
                    </x-field>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field name="phone" label="رقم الهاتف" required>
                            <input id="phone" name="phone" value="{{ old('phone') }}" required inputmode="tel" dir="ltr" autocomplete="tel" class="input text-start" @if ($errors->has('phone')) aria-invalid="true" aria-describedby="phone-error" @endif>
                        </x-field>
                        <x-field name="email" label="البريد الإلكتروني (اختياري)">
                            <input id="email" type="email" name="email" value="{{ old('email') }}" dir="ltr" autocomplete="email" class="input text-start" @if ($errors->has('email')) aria-invalid="true" aria-describedby="email-error" @endif>
                        </x-field>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-field name="city" label="المدينة" required>
                            <input id="city" name="city" value="{{ old('city') }}" required maxlength="100" autocomplete="address-level2" class="input" @if ($errors->has('city')) aria-invalid="true" aria-describedby="city-error" @endif>
                        </x-field>
                        <x-field name="address" label="العنوان بالتفصيل" required>
                            <input id="address" name="address" value="{{ old('address') }}" required maxlength="255" autocomplete="street-address" class="input" @if ($errors->has('address')) aria-invalid="true" aria-describedby="address-error" @endif>
                        </x-field>
                    </div>
                    <x-field name="notes" label="ملاحظات على الطلب (اختياري)">
                        <textarea id="notes" name="notes" rows="3" maxlength="1000" class="input" @if ($errors->has('notes')) aria-invalid="true" aria-describedby="notes-error" @endif>{{ old('notes') }}</textarea>
                    </x-field>
                </section>

                <section class="card card-pad space-y-3" aria-labelledby="sec-pay">
                    <h2 id="sec-pay" class="t-heading">طريقة الدفع</h2>
                    @foreach ($methods as $key => $method)
                        <label class="flex min-h-14 cursor-pointer items-start gap-3 rounded-xl border p-4 transition has-[:checked]:border-primary has-[:checked]:bg-primary-soft has-[:checked]:shadow-[0_0_0_1px_var(--store-primary)]" style="border-color: var(--line)">
                            <input type="radio" name="payment_method" value="{{ $key }}" class="mt-1.5 size-5 shrink-0" style="accent-color: var(--store-primary)" @checked(old('payment_method', array_key_first($methods)) === $key) required>
                            <span class="min-w-0">
                                <span class="block font-bold">{{ $method->label($settings) }}</span>
                                @if ($method->description($settings))
                                    <span class="t-small t-muted block whitespace-pre-line">{{ $method->description($settings) }}</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                    @error('payment_method')<p class="field-error"><x-icon name="alert" class="mt-0.5 size-4 shrink-0" /> {{ $message }}</p>@enderror
                </section>
            </div>

            {{-- ملخص الكمبيوتر + زر التأكيد --}}
            <aside class="card card-pad hidden space-y-4 md:sticky md:top-24 md:block" aria-label="ملخص الطلب">
                <h2 class="t-heading">ملخص الطلب</h2>
                @include('partials.order-summary')
                <x-button variant="primary" block class="!min-h-14 text-base">تأكيد الطلب</x-button>
                <p class="t-small t-muted">السعر النهائي يُحسب على الخادم عند تأكيد الطلب.</p>
            </aside>

            {{-- شريط تأكيد ثابت على الهاتف --}}
            <div class="sticky-bar md:hidden">
                <div class="flex items-center gap-4">
                    <div><p class="text-xs t-muted">الإجمالي</p><x-price :amount="$total" size="md" /></div>
                    <x-button variant="primary" class="flex-1">تأكيد الطلب</x-button>
                </div>
            </div>
        </form>
    @endif
@endsection
