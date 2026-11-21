@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <article class="mx-auto max-w-3xl">
        <h1 class="t-title mb-6">{{ $page->title }}</h1>
        {{-- المحتوى Markdown يُحوَّل إلى HTML بعد إزالة أي HTML خام --}}
        <div class="card card-pad"><div class="prose-ar !mt-0 [&>*:first-child]:mt-0">{!! $page->renderedBody() !!}</div></div>

        @if ($showContact)
            <dl class="card card-pad mt-5 space-y-3 text-[0.95rem]">
                @if ($settings->phone)<div class="flex flex-wrap gap-x-3"><dt class="font-bold">الهاتف</dt><dd><bdi dir="ltr">{{ $settings->phone }}</bdi></dd></div>@endif
                @if ($settings->email)<div class="flex flex-wrap gap-x-3"><dt class="font-bold">البريد</dt><dd><bdi dir="ltr">{{ $settings->email }}</bdi></dd></div>@endif
                @if ($settings->fullAddress())<div class="flex flex-wrap gap-x-3"><dt class="font-bold">العنوان</dt><dd>{{ $settings->fullAddress() }}</dd></div>@endif
            </dl>
        @endif
    </article>
@endsection
