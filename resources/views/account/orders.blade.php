@extends('layouts.app')
@section('title', 'طلباتي')
@section('content')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="text-xl font-bold">طلباتي</h1>
        <form action="{{ route('account.logout') }}" method="post">@csrf<button class="btn btn-outline" type="submit">تسجيل الخروج</button></form>
    </div>
    @if ($orders->isEmpty())
        <div class="card p-8 text-center"><p class="text-stone-700">لا توجد طلبات بعد.</p><a href="{{ route('products.index') }}" class="btn btn-primary mt-4">ابدأ التسوّق</a></div>
    @else
        <ul class="space-y-3">
            @foreach ($orders as $order)
                <li>
                    <a href="{{ route('account.order', $order->number) }}" class="card flex flex-wrap items-center justify-between gap-2 p-4 hover:border-primary">
                        <span><span class="block font-bold" dir="ltr">{{ $order->number }}</span><span class="text-sm text-stone-600">{{ $order->created_at->format('Y-m-d') }} · <bdi>{{ $order->items_count }}</bdi> منتج</span></span>
                        <span class="text-end"><span class="block rounded-full bg-secondary/60 px-3 py-0.5 text-sm font-semibold">{{ $order->status->label() }}</span><span class="font-bold"><bdi>{{ $store->money($order->total) }}</bdi></span></span>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-4">{{ $orders->links() }}</div>
    @endif
@endsection
