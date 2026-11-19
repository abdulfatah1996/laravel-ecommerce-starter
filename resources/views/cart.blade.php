@extends('layouts.app')

@section('title', 'سلة المشتريات')

@section('content')
    <h1 class="mb-4 text-xl font-bold">سلة المشتريات</h1>

    @if ($lines->isEmpty())
        <div class="card p-8 text-center">
            <p class="text-stone-500">سلتك فارغة.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-4">تصفّح المنتجات</a>
        </div>
    @else
        <div class="grid gap-6 md:grid-cols-3">
            <div class="space-y-3 md:col-span-2">
                @foreach ($lines as $line)
                    @php($product = $line['product'])
                    <div class="card flex gap-3 p-3">
                        <a href="{{ route('products.show', $product->slug) }}" class="block h-20 w-20 shrink-0 overflow-hidden rounded-lg bg-secondary/40">
                            @if ($product->primary_image_url)
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @endif
                        </a>
                        <div class="flex-1">
                            <a href="{{ route('products.show', $product->slug) }}" class="font-semibold hover:text-primary">{{ $product->name }}</a>
                            <p class="text-sm text-stone-500">{{ $store->money($line['unit_price']) }}</p>
                            @if ($line['exceeds_stock'])
                                <p class="mt-1 text-xs text-red-600">المتاح من هذا المنتج {{ $product->stock }} فقط — عدّل الكمية.</p>
                            @endif
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <form action="{{ route('cart.update', $product->id) }}" method="post" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label for="qty-{{ $product->id }}" class="sr-only">الكمية</label>
                                    <input id="qty-{{ $product->id }}" type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="{{ config('store.max_cart_quantity') }}" class="input w-20">
                                    <button type="submit" class="btn btn-outline">تحديث</button>
                                </form>
                                <form action="{{ route('cart.remove', $product->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline">إزالة</button>
                                </form>
                            </div>
                        </div>
                        <p class="font-bold">{{ $store->money($line['line_total']) }}</p>
                    </div>
                @endforeach
            </div>

            <aside class="card h-fit space-y-3 p-4" aria-label="ملخص الطلب">
                <h2 class="font-bold">ملخص الطلب</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt>المجموع الفرعي</dt><dd>{{ $store->money($subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt>الشحن</dt><dd>{{ $shipping > 0 ? $store->money($shipping) : 'مجاني' }}</dd></div>
                    <div class="flex justify-between border-t border-stone-200 pt-2 text-base font-bold"><dt>الإجمالي</dt><dd>{{ $store->money($total) }}</dd></div>
                </dl>
                <a href="{{ route('checkout.show') }}" class="btn btn-primary w-full">إتمام الطلب</a>
            </aside>
        </div>
    @endif
@endsection
