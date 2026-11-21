{{-- ملخص مكوّن من بنود وتفاصيل التكلفة: يُستخدم في الدفع (هاتف وكمبيوتر) --}}
<ul class="space-y-3">
    @foreach ($lines as $line)
        <li class="flex items-center gap-3">
            <span class="relative block size-14 shrink-0 overflow-hidden rounded-xl" style="background: var(--tint)">
                @if ($line['product']->primary_image_url)<img src="{{ $line['product']->primary_image_url }}" alt="" class="size-full object-cover">@endif
                <span class="absolute -end-0 -top-0 flex min-w-5 items-center justify-center rounded-bl-lg bg-primary px-1 text-[0.7rem] font-bold leading-5 text-on-primary"><bdi dir="ltr">{{ $line['quantity'] }}</bdi></span>
            </span>
            <span class="min-w-0 flex-1 text-sm font-medium leading-6">{{ $line['product']->name }}</span>
            <x-price :amount="$line['line_total']" size="sm" class="shrink-0" />
        </li>
    @endforeach
</ul>
<dl class="mt-4 space-y-2.5 border-t pt-4 text-[0.95rem]" style="border-color: var(--line)">
    <div class="flex items-center justify-between gap-3"><dt class="t-muted">المجموع الفرعي</dt><dd><x-price :amount="$subtotal" size="sm" /></dd></div>
    <div class="flex items-center justify-between gap-3"><dt class="t-muted">الشحن</dt><dd class="font-bold">@if ($shipping > 0)<x-price :amount="$shipping" size="sm" />@else مجاني @endif</dd></div>
    <div class="flex items-center justify-between gap-3 border-t pt-3" style="border-color: var(--line)"><dt class="text-lg font-bold">الإجمالي</dt><dd><x-price :amount="$total" size="lg" class="!text-xl" /></dd></div>
</dl>
