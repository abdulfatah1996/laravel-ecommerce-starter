@props(['title', 'text' => null, 'icon' => 'bag'])
<div {{ $attributes->merge(['class' => 'card flex flex-col items-center px-6 py-12 text-center']) }}>
    <span class="mb-4 flex size-14 items-center justify-center rounded-full bg-primary-soft text-primary"><x-icon :name="$icon" class="size-7" /></span>
    <p class="t-heading">{{ $title }}</p>
    @if ($text)<p class="t-muted mt-1 max-w-sm">{{ $text }}</p>@endif
    @if (trim((string) $slot) !== '')<div class="mt-5">{{ $slot }}</div>@endif
</div>
