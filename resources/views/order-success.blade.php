@extends('layouts.app')

@section('title', 'تم استلام طلبك')

@section('content')
    <div class="mx-auto max-w-xl space-y-5">
        <div class="card card-pad text-center">
            <span class="mx-auto flex size-16 items-center justify-center rounded-full bg-primary text-on-primary"><x-icon name="check" class="size-8" /></span>
            <h1 class="t-title mt-4">شكرًا لك! تم استلام طلبك</h1>
            <p class="t-muted mt-1">سيتواصل معك المتجر لتأكيد التوصيل.</p>

            <p class="t-small t-muted mt-6">رقم طلبك</p>
            <p class="mt-1 rounded-xl py-3 text-2xl font-bold tracking-wider" style="background: var(--tint)" dir="ltr" data-order-number>{{ $order->number }}</p>
            <p class="t-small t-muted mt-2">احتفظ بهذا الرقم للمراجعة والتواصل مع المتجر.</p>
        </div>

        <section class="card card-pad" aria-labelledby="sum">
            <h2 id="sum" class="t-heading mb-4">تفاصيل الطلب</h2>
            <ul class="space-y-3">
                @foreach ($order->items as $item)
                    <li class="flex items-start justify-between gap-3 text-[0.95rem]">
                        <span class="min-w-0 leading-6">{{ $item->name }} <span class="t-muted">× <bdi dir="ltr">{{ $item->quantity }}</bdi></span></span>
                        <x-price :amount="$item->line_total" size="sm" class="shrink-0" />
                    </li>
                @endforeach
            </ul>
            <dl class="mt-4 space-y-2.5 border-t pt-4 text-[0.95rem]" style="border-color: var(--line)">
                <div class="flex items-center justify-between gap-3"><dt class="t-muted">الشحن</dt><dd class="font-bold">@if ((float) $order->shipping_fee > 0)<x-price :amount="$order->shipping_fee" size="sm" />@else مجاني @endif</dd></div>
                <div class="flex items-center justify-between gap-3"><dt class="text-lg font-bold">الإجمالي</dt><dd><x-price :amount="$order->total" size="lg" class="!text-xl" /></dd></div>
            </dl>

            @if ($method)
                <div class="mt-5 rounded-xl p-4 text-sm" style="background: var(--tint)">
                    <p class="font-bold">طريقة الدفع: {{ $method->label($settings) }}</p>
                    @if ($method->description($settings))
                        <p class="t-muted mt-1 whitespace-pre-line leading-7">{{ $method->description($settings) }}</p>
                    @endif
                </div>
            @endif
        </section>

        <x-button :href="route('home')" variant="primary" block>متابعة التسوّق</x-button>
    </div>
@endsection
