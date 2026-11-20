@extends('layouts.app')

@section('title', 'تم استلام طلبك')

@section('content')
    <div class="card mx-auto max-w-xl space-y-4 p-6 text-center">
        <h1 class="text-xl font-bold text-primary">شكرًا لك! تم استلام طلبك</h1>
        <p class="text-stone-700">رقم طلبك</p>
        <p class="rounded-xl bg-secondary/40 py-3 text-2xl font-bold tracking-wider" dir="ltr" data-order-number>{{ $order->number }}</p>
        <p class="text-sm text-stone-600">احتفظ بهذا الرقم للمراجعة والتواصل مع المتجر.</p>

        <dl class="space-y-2 border-t border-stone-200 pt-4 text-start text-sm">
            @foreach ($order->items as $item)
                <div class="flex justify-between gap-2"><dt>{{ $item->name }}@if($item->variant_label) ({{ $item->variant_label }})@endif × <bdi>{{ $item->quantity }}</bdi></dt><dd><bdi>{{ $store->money($item->line_total) }}</bdi></dd></div>
            @endforeach
            <div class="flex justify-between"><dt>الشحن@if($order->shipping_zone) ({{ $order->shipping_zone }})@endif</dt><dd><bdi>{{ (float) $order->shipping_fee > 0 ? $store->money($order->shipping_fee) : 'مجاني' }}</bdi></dd></div>
            <div class="flex justify-between text-base font-bold"><dt>الإجمالي</dt><dd data-order-total><bdi>{{ $store->money($order->total) }}</bdi></dd></div>
        </dl>

        @if ($method)
            <div class="rounded-xl border border-stone-200 p-3 text-start text-sm">
                <p class="font-semibold">طريقة الدفع: {{ $method->label($settings) }}</p>
                @if ($method->description($settings))
                    <p class="mt-1 whitespace-pre-line text-stone-700">{{ $method->description($settings) }}</p>
                @endif
            </div>
        @endif

        <div class="flex flex-col gap-2 sm:flex-row sm:justify-center">
            <a href="{{ route('home') }}" class="btn btn-primary">متابعة التسوّق</a>
            @auth
                <a href="{{ route('account.order', $order->number) }}" class="btn btn-outline">تفاصيل الطلب</a>
            @endauth
            @if ($store->whatsappUrl())
                <a href="{{ $store->whatsappUrl('مرحبًا، رقم طلبي '.$order->number) }}" target="_blank" rel="noopener" class="btn btn-outline">تأكيد عبر واتساب</a>
            @endif
        </div>
    </div>
@endsection
