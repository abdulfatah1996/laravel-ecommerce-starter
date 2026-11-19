@extends('layouts.app')

@section('title', 'تم استلام طلبك')

@section('content')
    <div class="card mx-auto max-w-xl space-y-4 p-6 text-center">
        <h1 class="text-xl font-extrabold text-primary">شكرًا لك! تم استلام طلبك</h1>
        <p class="text-stone-600">رقم طلبك:</p>
        <p class="rounded-lg bg-secondary/40 py-3 text-2xl font-bold tracking-wider" dir="ltr">{{ $order->number }}</p>
        <p class="text-sm text-stone-500">احتفظ بهذا الرقم للمراجعة والتواصل مع المتجر.</p>

        <dl class="space-y-2 border-t border-stone-200 pt-4 text-start text-sm">
            @foreach ($order->items as $item)
                <div class="flex justify-between gap-2"><dt>{{ $item->name }} × {{ $item->quantity }}</dt><dd>{{ $store->money($item->line_total) }}</dd></div>
            @endforeach
            <div class="flex justify-between"><dt>الشحن</dt><dd>{{ (float) $order->shipping_fee > 0 ? $store->money($order->shipping_fee) : 'مجاني' }}</dd></div>
            <div class="flex justify-between text-base font-bold"><dt>الإجمالي</dt><dd>{{ $store->money($order->total) }}</dd></div>
        </dl>

        @if ($method)
            <div class="rounded-lg border border-stone-200 p-3 text-start text-sm">
                <p class="font-semibold">طريقة الدفع: {{ $method->label($settings) }}</p>
                @if ($method->description($settings))
                    <p class="mt-1 whitespace-pre-line text-stone-600">{{ $method->description($settings) }}</p>
                @endif
            </div>
        @endif

        <a href="{{ route('home') }}" class="btn btn-primary">متابعة التسوّق</a>
    </div>
@endsection
