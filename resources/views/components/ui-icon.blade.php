@props(['name', 'class' => 'size-5'])
@php
    $paths = [
        'cart' => 'M2.25 3h1.4a1 1 0 0 1 .97.76L5.1 6m0 0 1.4 6.5a1 1 0 0 0 .98.8h9.2a1 1 0 0 0 .96-.72L19.5 6H5.1ZM9 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm8.25 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z',
        'search' => 'm21 21-4.8-4.8m1.8-5.2a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z',
        'menu' => 'M4 7h16M4 12h16M4 17h16',
        'close' => 'M6 6l12 12M18 6 6 18',
        'check' => 'm5 12.5 4.5 4.5L19 7.5',
        'chevron-down' => 'm6 9 6 6 6-6',
        'chevron-left' => 'm15 5-7 7 7 7',
        'trash' => 'M6 7h12m-1 0-.7 11.2a1.5 1.5 0 0 1-1.5 1.4H9.2a1.5 1.5 0 0 1-1.5-1.4L7 7m3-3h4a1 1 0 0 1 1 1v2H9V5a1 1 0 0 1 1-1Z',
        'truck' => 'M3 6.5h10v9H3v-9Zm10 3h4l3 3v3h-7v-6ZM7 18.5a1.75 1.75 0 1 1-3.5 0 1.75 1.75 0 0 1 3.5 0Zm11 0a1.75 1.75 0 1 1-3.5 0 1.75 1.75 0 0 1 3.5 0Z',
        'cash' => 'M3 7.5h18v9H3v-9Zm9 6.5a2 2 0 1 0 0-4 2 2 0 0 0 0 4ZM6.5 10v.01M17.5 14v.01',
        'phone' => 'M5 4h3l1.5 4-2 1.2a11 11 0 0 0 6.3 6.3L15 13.5l4 1.5v3a2 2 0 0 1-2 2A14 14 0 0 1 3 6a2 2 0 0 1 2-2Z',
        'alert' => 'M12 9v4m0 3.5v.01M10.3 4.2 2.9 17a2 2 0 0 0 1.7 3h14.8a2 2 0 0 0 1.7-3L13.7 4.2a2 2 0 0 0-3.4 0Z',
        'bag' => 'M5 8h14l-1 11.2a1.5 1.5 0 0 1-1.5 1.3h-9A1.5 1.5 0 0 1 6 19.2L5 8Zm3.5 0V6.5a3.5 3.5 0 0 1 7 0V8',
        'filter' => 'M4 6h16M7 12h10M10 18h4',
        'image' => 'M3 16.5 8 11l4 4 3-3 6 6M4.5 4h15A1.5 1.5 0 0 1 21 5.5v13a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 18.5v-13A1.5 1.5 0 0 1 4.5 4Z',
    ];
@endphp
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes->merge(['class' => $class]) }}><path d="{{ $paths[$name] ?? '' }}"/></svg>
