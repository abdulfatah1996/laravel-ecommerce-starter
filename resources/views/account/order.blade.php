@extends('layouts.app')
@section('title', 'طلب '.$order->number)
@section('content')
    <a href="{{ route('account.orders') }}" class="mb-3 inline-block text-sm font-semibold text-primary">← كل طلباتي</a>
    <div class="card space-y-4 p-5">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-xl font-bold" dir="ltr">{{ $order->number }}</h1>
            <span class="rounded-full bg-secondary/60 px-3 py-0.5 text-sm font-semibold">{{ $order->status->label() }}</span>
        </div>
        <dl class="space-y-2 border-t border-stone-200 pt-4 text-sm">
            @foreach ($order->items as $item)
                <div class="flex justify-between gap-2"><dt>{{ $item->name }}@if($item->variant_label) ({{ $item->variant_label }})@endif × <bdi>{{ $item->quantity }}</bdi></dt><dd><bdi>{{ $store->money($item->line_total) }}</bdi></dd></div>
            @endforeach
            <div class="flex justify-between"><dt>الشحن@if($order->shipping_zone) ({{ $order->shipping_zone }})@endif</dt><dd><bdi>{{ (float) $order->shipping_fee > 0 ? $store->money($order->shipping_fee) : 'مجاني' }}</bdi></dd></div>
            <div class="flex justify-between text-base font-bold"><dt>الإجمالي</dt><dd><bdi>{{ $store->money($order->total) }}</bdi></dd></div>
        </dl>
        <p class="text-sm text-stone-700">التوصيل إلى: {{ $order->city }}، {{ $order->address }}</p>
    </div>
@endsection
