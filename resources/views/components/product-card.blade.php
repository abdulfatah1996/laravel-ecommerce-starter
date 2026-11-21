@props(['product', 'priority' => false])
@php($inStock = $product->isInStock())
<article class="product-card flex min-w-0 flex-col gap-3">
    <a href="{{ route('products.show', $product->slug) }}" class="product-img" tabindex="-1" aria-hidden="true">
        @if ($product->primary_image_url)
            <img src="{{ $product->primary_image_url }}" alt="" @if (! $priority) loading="lazy" @endif @class(['opacity-60' => ! $inStock])>
        @else
            <span class="flex size-full items-center justify-center text-primary/40"><x-ui-icon name="image" class="size-12" /></span>
        @endif
        @if ($product->hasDiscount() && $inStock)
            <span class="badge badge-sale absolute start-2.5 top-2.5">خصم <bdi dir="ltr">{{ (int) round((1 - (float) $product->price / (float) $product->compare_at_price) * 100) }}%</bdi></span>
        @endif
        @unless ($inStock)
            <span class="badge badge-muted absolute start-2.5 top-2.5">غير متوفر</span>
        @endunless
    </a>
    <div class="flex flex-1 flex-col gap-1.5 px-0.5">
        <a href="{{ route('products.show', $product->slug) }}" class="product-name">{{ $product->name }}</a>
        <x-price :amount="$product->price" :compare="$product->compare_at_price" size="sm" />
    </div>
    @if ($inStock)
        <form action="{{ route('cart.add', $product->id) }}" method="post">
            @csrf
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn btn-outline btn-sm btn-block"><x-ui-icon name="cart" class="size-4" /> أضف للسلة</button>
        </form>
    @else
        <span class="btn btn-sm btn-block cursor-default bg-stone-100 text-stone-600" aria-disabled="true">غير متوفر حاليًا</span>
    @endif
</article>
