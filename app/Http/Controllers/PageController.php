<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\StoreSetting;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $slug): View
    {
        // صفحة افتراضية جديدة لم تُنشأ بعد في متجر قائم: تُنشأ عند أول زيارة.
        if (isset(Page::defaults()[$slug])) {
            Page::firstOrCreate(['slug' => $slug], Page::defaults()[$slug] + ['is_published' => true]);
        }

        $page = Page::query()->where('slug', $slug)->where('is_published', true)->firstOrFail();

        return view('page', [
            'page' => $page,
            'showContact' => $slug === 'contact',
            'settings' => StoreSetting::current(),
        ]);
    }
}
