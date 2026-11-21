@props(['type' => 'ok'])
<div role="{{ $type === 'error' ? 'alert' : 'status' }}" {{ $attributes->merge(['class' => 'alert alert-'.$type]) }}>
    <x-ui-icon :name="$type === 'error' ? 'alert' : 'check'" class="mt-0.5 size-5 shrink-0" />
    <div class="min-w-0 flex-1">{{ $slot }}</div>
</div>
