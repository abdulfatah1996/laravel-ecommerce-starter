<article class="card flex flex-col overflow-hidden">
    <a href="{{ route('products.show', $product->slug) }}" class="block aspect-square bg-secondary/40">
        @if ($product->primary_image_url)
            <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" loading="lazy" class="h-full w-full object-cover">
        @else
            <div class="flex h-full w-full items-center justify-center text-stone-400" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.16-5.16a2.25 2.25 0 013.18 0l5.16 5.16m-1.5-1.5l1.41-1.41a2.25 2.25 0 013.18 0l2.41 2.41M3.75 21h16.5A1.5 1.5 0 0021.75 19.5v-15A1.5 1.5 0 0020.25 3H3.75A1.5 1.5 0 002.25 4.5v15A1.5 1.5 0 003.75 21z"/></svg>
            </div>
        @endif
    </a>
    <div class="flex flex-1 flex-col gap-2 p-3">
        <a href="{{ route('products.show', $product->slug) }}" class="line-clamp-2 text-sm font-semibold hover:text-primary">{{ $product->name }}</a>
        <div class="flex items-baseline gap-2">
            <span class="font-bold text-primary">{{ $store->money($product->price) }}</span>
            @if ($product->hasDiscount())
                <span class="text-xs text-stone-400 line-through">{{ $store->money($product->compare_at_price) }}</span>
            @endif
        </div>
        @if ($product->isInStock())
            <form action="{{ route('cart.add', $product->id) }}" method="post" class="mt-auto">
                @csrf
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-primary w-full">أضف إلى السلة</button>
            </form>
        @else
            <span class="mt-auto rounded-lg bg-stone-100 py-2 text-center text-xs text-stone-500">غير متوفر</span>
        @endif
    </div>
</article>
