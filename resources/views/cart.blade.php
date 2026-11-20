@extends('layouts.app')

@section('title', 'سلة المشتريات')
@section('hide_wa', '1')

@section('content')
    <h1 class="mb-4 text-xl font-bold">سلة المشتريات</h1>

    @if ($lines->isEmpty())
        <div class="card p-8 text-center">
            <p class="text-stone-600">سلتك فارغة.</p>
            <a href="{{ route('products.index') }}" class="btn btn-primary mt-4">تصفّح المنتجات</a>
        </div>
    @else
        <div class="grid gap-5 md:grid-cols-3">
            <div class="min-w-0 space-y-3 md:col-span-2">
                @foreach ($lines as $line)
                    @php($product = $line['product'])
                    <div class="card flex min-w-0 gap-3 p-3" data-cart-line>
                        <a href="{{ route('products.show', $product->slug) }}" class="block size-24 shrink-0 overflow-hidden rounded-xl bg-secondary/40">
                            @if ($product->primary_image_url)
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @endif
                        </a>
                        <div class="flex min-w-0 flex-1 flex-col">
                            <a href="{{ route('products.show', $product->slug) }}" class="line-clamp-2 font-semibold leading-6 hover:text-primary">{{ $product->name }}</a>
                            @if ($line['variant'])
                                <p class="text-sm text-stone-600">{{ $product->option_name ?: 'الخيار' }}: {{ $line['variant']->label }}</p>
                            @endif
                            <p class="text-sm text-stone-600"><bdi>{{ $store->money($line['unit_price']) }}</bdi></p>
                            @if ($line['exceeds_stock'])
                                <p class="mt-1 text-xs font-semibold text-red-700">المتاح <bdi>{{ $line['stock'] }}</bdi> فقط — عدّل الكمية.</p>
                            @endif
                            <div class="mt-auto flex flex-wrap items-center justify-between gap-2 pt-2">
                                <form action="{{ route('cart.update', $line['key']) }}" method="post" class="flex items-center gap-2" data-autosubmit>
                                    @csrf
                                    @method('PATCH')
                                    <label for="qty-{{ $loop->index }}" class="sr-only">الكمية</label>
                                    <input id="qty-{{ $loop->index }}" type="number" name="quantity" value="{{ $line['quantity'] }}" min="1" max="{{ min(config('store.max_cart_quantity'), max(1, $line['stock'])) }}" inputmode="numeric" class="input !w-20 min-w-0 text-center">
                                    <button type="submit" class="btn btn-outline">تحديث</button>
                                </form>
                                <form action="{{ route('cart.remove', $line['key']) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="min-h-11 px-2 text-sm font-semibold text-red-700 hover:underline">حذف</button>
                                </form>
                            </div>
                        </div>
                        <p class="shrink-0 whitespace-nowrap text-sm font-bold"><bdi>{{ $store->money($line['line_total']) }}</bdi></p>
                    </div>
                @endforeach
            </div>

            <aside class="card h-fit min-w-0 space-y-3 p-4" aria-label="ملخص الطلب">
                <h2 class="font-bold">ملخص الطلب</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt>المجموع الفرعي</dt><dd><bdi>{{ $store->money($subtotal) }}</bdi></dd></div>
                    <div class="flex justify-between gap-3">
                        <dt class="shrink-0">الشحن</dt>
                        <dd class="min-w-0 text-end">
                            @if ($shippingRange['uses_zones'])
                                حسب منطقتك، من <bdi>{{ $store->money($shippingRange['min']) }}</bdi>
                            @else
                                <bdi>{{ $shippingRange['min'] > 0 ? $store->money($shippingRange['min']) : 'مجاني' }}</bdi>
                            @endif
                        </dd>
                    </div>
                </dl>
                <a href="{{ route('checkout.show') }}" class="btn btn-primary btn-lg w-full">إتمام الطلب</a>
                <p class="text-center text-xs text-stone-600">يمكنك إتمام الطلب كضيف بدون حساب.</p>
            </aside>
        </div>
    @endif
@endsection
