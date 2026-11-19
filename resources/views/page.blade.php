@extends('layouts.app')

@section('title', $page->title)

@section('content')
    <article class="card mx-auto max-w-3xl p-6">
        <h1 class="mb-4 text-2xl font-extrabold">{{ $page->title }}</h1>
        {{-- المحتوى Markdown يُحوَّل إلى HTML بعد إزالة أي HTML خام --}}
        <div class="prose-ar">{!! $page->renderedBody() !!}</div>

        @if ($showContact)
            <dl class="mt-6 space-y-2 border-t border-stone-200 pt-4 text-sm">
                @if ($settings->phone)<div class="flex gap-2"><dt class="font-semibold">الهاتف:</dt><dd dir="ltr">{{ $settings->phone }}</dd></div>@endif
                @if ($settings->email)<div class="flex gap-2"><dt class="font-semibold">البريد:</dt><dd>{{ $settings->email }}</dd></div>@endif
                @if ($settings->fullAddress())<div class="flex gap-2"><dt class="font-semibold">العنوان:</dt><dd>{{ $settings->fullAddress() }}</dd></div>@endif
            </dl>
        @endif
    </article>
@endsection
