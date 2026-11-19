<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\InstallRequest;
use App\Models\Page;
use App\Models\StoreSetting;
use App\Models\User;
use App\Support\InstallState;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

/**
 * التثبيت الأول — مرة واحدة فقط.
 *  - لا يطلب ولا يعرض بيانات قاعدة البيانات (مكانها ملف .env على الخادم).
 *  - بعد النجاح تُنشأ ملف قفل ويصبح المسار 404 (middleware not.installed).
 *  - في production لا يعمل دون INSTALL_TOKEN.
 */
class InstallController extends Controller
{
    public function show(): View
    {
        if (! InstallState::databaseReady()) {
            return view('install.blocked', ['reason' => 'database']);
        }

        if (app()->isProduction() && ! InstallState::tokenConfigured()) {
            return view('install.blocked', ['reason' => 'token']);
        }

        return view('install.form', [
            'tokenRequired' => InstallState::tokenRequired(),
            'currencies' => config('store.currencies'),
            'timezones' => DateTimeZone::listIdentifiers(),
            'defaults' => config('store.default_colors'),
        ]);
    }

    public function store(InstallRequest $request): RedirectResponse
    {
        abort_unless(InstallState::databaseReady(), 503);
        abort_unless(InstallState::tokenIsValid($request->input('install_token')), 403, 'رمز التثبيت غير صحيح.');

        // قفل ذرّي: أول طلب فقط ينجح، أي طلب متزامن آخر يحصل على 404.
        abort_unless(InstallState::acquire(), 404);

        $logoPath = null;

        try {
            DB::transaction(function () use ($request, &$logoPath) {
                if ($request->hasFile('logo')) {
                    $logoPath = $request->file('logo')->store('branding', 'public');
                }

                $currency = config('store.currencies')[$request->input('currency_code')];

                $settings = StoreSetting::query()->firstOrNew();
                $settings->fill([
                    'name' => $request->input('store_name'),
                    'description' => $request->input('description'),
                    'logo' => $logoPath,
                    'email' => $request->input('email'),
                    'phone' => $request->input('phone'),
                    'country' => $request->input('country'),
                    'city' => $request->input('city'),
                    'address' => $request->input('address'),
                    'currency_code' => $request->input('currency_code'),
                    'currency_symbol' => $currency['symbol'],
                    'timezone' => $request->input('timezone'),
                    'locale' => $request->input('locale'),
                    'primary_color' => $request->input('primary_color'),
                    'secondary_color' => $request->input('secondary_color'),
                    'accent_color' => $request->input('accent_color'),
                    'cod_enabled' => true,
                    'installed_at' => now(),
                ])->save();

                $user = User::create([
                    'name' => $request->input('owner_name'),
                    'email' => $request->input('owner_email'),
                    'password' => $request->input('owner_password'),
                    'role' => UserRole::Admin,
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]);

                foreach (Page::defaults() as $slug => $page) {
                    Page::firstOrCreate(['slug' => $slug], $page + ['is_published' => true]);
                }

                Auth::login($user);
            });
        } catch (Throwable $e) {
            // نسجّل اسم الخطأ فقط (بدون رسالته) كي لا تتسرب أي بيانات حساسة للسجلات.
            Log::error('Store installation failed: '.$e::class);

            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }
            InstallState::release();

            return back()->withInput()->withErrors(['install' => 'تعذر إكمال التثبيت. تحقق من إعدادات الخادم وحاول مرة أخرى.']);
        }

        $request->session()->regenerate();

        return redirect('/admin')->with('status', 'تم تجهيز المتجر بنجاح.');
    }
}
