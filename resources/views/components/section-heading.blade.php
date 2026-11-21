@props(['title', 'id' => null, 'href' => null, 'linkLabel' => 'عرض الكل'])
<div class="section-head">
    <h2 @if ($id) id="{{ $id }}" @endif class="t-heading">{{ $title }}</h2>
    @if ($href)
        <a href="{{ $href }}" class="inline-flex min-h-11 items-center gap-1 text-sm font-bold text-primary">{{ $linkLabel }} <x-ui-icon name="chevron-left" class="size-4" /></a>
    @endif
</div>
