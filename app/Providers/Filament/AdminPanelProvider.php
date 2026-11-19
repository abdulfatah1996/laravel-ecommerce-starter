<?php

namespace App\Providers\Filament;

use App\Http\Middleware\ApplyStoreSettings;
use App\Models\StoreSetting;
use App\Support\Colors;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Throwable;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // اسم المتجر وشعاره ولونه الأساسي يأتون من إعدادات المتجر؛
        // وإن لم تكن قاعدة البيانات جاهزة (قبل migrate) نستخدم قيمًا افتراضية بدل كسر التطبيق.
        $name = (string) config('app.name');
        $logo = null;
        $primary = Color::Teal;

        try {
            $settings = StoreSetting::current();
            $name = $settings->name ?: $name;
            $logo = $settings->logo_url;

            if (Colors::isHex($settings->primary_color)) {
                $primary = Color::hex($settings->primary_color);
            }
        } catch (Throwable) {
            // تجاهل: قيم افتراضية
        }

        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName($name)
            ->brandLogo($logo)
            ->brandLogoHeight('2.5rem')
            ->colors(['primary' => $primary])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([Dashboard::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                ApplyStoreSettings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
