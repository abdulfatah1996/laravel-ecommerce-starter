@if ($paginator->hasPages())
    <nav role="navigation" aria-label="التنقل بين الصفحات" class="flex flex-wrap items-center justify-center gap-2">
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm pointer-events-none opacity-40" aria-disabled="true">السابق</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">السابق</a>
        @endif

        <span class="hidden items-center gap-1.5 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="px-1 text-stone-400" aria-hidden="true">…</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex size-11 items-center justify-center rounded-xl bg-primary font-bold text-on-primary"><bdi dir="ltr">{{ $page }}</bdi></span>
                        @else
                            <a href="{{ $url }}" class="inline-flex size-11 items-center justify-center rounded-xl font-bold hover:bg-primary-soft" aria-label="الصفحة {{ $page }}"><bdi dir="ltr">{{ $page }}</bdi></a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </span>
        <span class="t-small t-muted sm:hidden">صفحة <bdi dir="ltr">{{ $paginator->currentPage() }}</bdi> من <bdi dir="ltr">{{ $paginator->lastPage() }}</bdi></span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">التالي</a>
        @else
            <span class="btn btn-outline btn-sm pointer-events-none opacity-40" aria-disabled="true">التالي</span>
        @endif
    </nav>
@endif
