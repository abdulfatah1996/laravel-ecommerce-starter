{{-- رأس مشترك: ألوان المتجر (من الإعدادات) + الأصول. ‏يُستخدم في كل القوالب بما فيها التثبيت. --}}
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="{{ $themeColor }}">
<style>
    :root {
        --store-primary: {{ $primary }};
        --store-secondary: {{ $secondary }};
        --store-accent: {{ $accent }};
        --store-on-primary: {{ $onPrimary }};
        --store-on-accent: {{ $onAccent }};
    }
</style>
@vite(['resources/css/app.css', 'resources/js/app.js'])
