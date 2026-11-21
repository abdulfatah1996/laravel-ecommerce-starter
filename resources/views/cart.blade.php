@extends('layouts.app')

@section('title', 'سلة المشتريات')
@section('body_class', 'pb-28 md:pb-0')

@section('content')
    <h1 class="t-title mb-5">سلة المشتريات @unless ($lines->isEmpty())<span class="t-small t-muted font-medium">(<bdi dir="ltr">{{ $lines->count() }}</bdi>)</span>@endunless</h1>

    @if ($lines->isEmpty())
        <x-empty-state icon="cart" title="سلتك فارغة" text="أضف منتجات إلى السلة لتظهر هنا.">
            <x-button :href="route('products.index')" variant="primary">تصفّح المنتجات</x-button>
        </x-empty-state>
    @else
        <div class="grid gap-6 md:grid-cols-[1fr_22rem] md:items-start md:gap-8">
            <ul class="min-w-0 space-y-3" aria-label="منتجات السلة">
                @foreach ($lines as $line)
                    @php($product = $line['product'])
                    <li class="card flex gap-3 p-3 md:gap-4 md:p-4">
                        <a href="{{ route('products.show', $product->slug) }}" class="block size-20 shrink-0 overflow-hidden rounded-xl md:size-28" style="background: var(--tint)" tabindex="-1" aria-hidden="true">
                            @if ($product->primary_image_url)
                                <img src="{{ $product->primary_image_url }}" alt="" class="size-full object-cover">
                            @endif
                        </a>
                        <div class="flex min-w-0 flex-1 flex-col gap-2">
                            <div class="flex items-start justify-between gap-3">
                                <a href="{{ route('products.show', $product->slug) }}" class="min-w-0 font-bold leading-6 hover:text-primary">{{ $product->name }}</a>
                                <x-price :amount="$line['line_total']" size="md" class="shrink-0" />
                            </div>
                            <p class="t-small t-muted">سعر الوحدة: <x-price :amount="$line['unit_price']" size="sm" class="!inline-flex" /></p>
                            @if ($line['exceeds_stock'])
                                <p class="field-error !mt-0"><x-icon name="alert" class="mt-0.5 size-4 shrink-0" /> المتاح من هذا المنتج <bdi dir="ltr">{{ $product->stock }}</bdi> فقط — عدّل الكمية.</p>
                            @endif
                            <div class="mt-auto flex flex-wrap items-center justify-between gap-2">
                                <form action="{{ route('cart.update', $product->id) }}" method="post" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <x-qty :id="'qty-'.$product->id" :value="$line['quantity']" :max="config('store.max_cart_quantity')" :autosubmit="true" />
                                    <noscript><button type="submit" class="btn btn-outline btn-sm">تحديث</button></noscript>
                                </form>
                                <form action="{{ route('cart.remove', $product->id) }}" method="post">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" aria-label="إزالة {{ $product->name }} من السلة"><x-icon name="trash" class="size-4" /> إزالة</button>
                                </form>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>

            <aside class="card card-pad space-y-4 md:sticky md:top-24" aria-label="ملخص الطلب">
                <h2 class="t-heading">ملخص الطلب</h2>
                <dl class="space-y-2.5 text-[0.95rem]">
                    <div class="flex items-center justify-between gap-3"><dt class="t-muted">المجموع الفرعي</dt><dd><x-price :amount="$subtotal" size="sm" /></dd></div>
                    <div class="flex items-center justify-between gap-3"><dt class="t-muted">الشحن</dt><dd class="font-bold">@if ($shipping > 0)<x-price :amount="$shipping" size="sm" />@else مجاني @endif</dd></div>
                    <div class="flex items-center justify-between gap-3 border-t pt-3 text-lg" style="border-color: var(--line)"><dt class="font-bold">الإجمالي</dt><dd><x-price :amount="$total" size="lg" class="!text-xl" /></dd></div>
                </dl>
                <x-button :href="route('checkout.show')" variant="primary" block class="hidden md:inline-flex">إتمام الطلب</x-button>
                <p class="t-small t-muted">الدفع عند إتمام الطلب. تظهر طرق الدفع المتاحة في الخطوة التالية.</p>
            </aside>
        </div>

        <div class="sticky-bar md:hidden">
            <div class="flex items-center gap-4">
                <div><p class="text-xs t-muted">الإجمالي</p><x-price :amount="$total" size="md" /></div>
                <x-button :href="route('checkout.show')" variant="primary" class="flex-1">إتمام الطلب</x-button>
            </div>
        </div>
    @endif
@endsection
