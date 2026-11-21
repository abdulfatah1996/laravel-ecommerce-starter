{{-- السعر: الرقم يُعزل ثنائي الاتجاه كي لا ينقلب مع رمز العملة داخل النص العربي --}}
@props(['amount', 'compare' => null, 'size' => 'md', 'badge' => false])
@php
    $store = \App\Models\StoreSetting::current();
    $fmt = fn ($v) => number_format((float) $v, 2);
    $hasDiscount = $compare !== null && (float) $compare > (float) $amount;
    $percent = $hasDiscount ? (int) round((1 - (float) $amount / (float) $compare) * 100) : 0;
@endphp
<span {{ $attributes->class(['price', 'price-'.$size]) }}>
    <bdi class="price-now"><span dir="ltr">{{ $fmt($amount) }}</span> <span class="font-medium">{{ $store->currency_symbol }}</span></bdi>
    @if ($hasDiscount)
        <bdi class="price-was"><span dir="ltr">{{ $fmt($compare) }}</span> {{ $store->currency_symbol }}</bdi>
        @if ($badge && $percent > 0)
            <span class="badge badge-sale">خصم <bdi dir="ltr">{{ $percent }}%</bdi></span>
        @endif
    @endif
</span>
