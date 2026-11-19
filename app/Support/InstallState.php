<?php

namespace App\Support;

use App\Models\StoreSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * حالة التثبيت الأول. الحماية من إعادة الاستخدام بطبقتين:
 *  1) ملف قفل يُنشأ بشكل ذرّي (fopen mode 'x') قبل أي كتابة في قاعدة البيانات.
 *  2) صف إعدادات المتجر الذي يحمل installed_at.
 */
final class InstallState
{
    public static function lockPath(): string
    {
        return (string) config('store.install_lock_path');
    }

    public static function isInstalled(): bool
    {
        if (is_file(self::lockPath())) {
            return true;
        }

        try {
            return StoreSetting::query()->whereNotNull('installed_at')->exists();
        } catch (Throwable) {
            return false;
        }
    }

    /** يحاول إنشاء ملف القفل ذرّيًا. false = سبقه تثبيت آخر أو تعذرت الكتابة. */
    public static function acquire(): bool
    {
        $path = self::lockPath();
        $dir = dirname($path);

        if (! is_dir($dir) && ! @mkdir($dir, 0775, true) && ! is_dir($dir)) {
            return false;
        }

        $handle = @fopen($path, 'x');
        if ($handle === false) {
            return false;
        }

        fwrite($handle, 'installed_at='.date('c').PHP_EOL);
        fclose($handle);

        return true;
    }

    public static function release(): void
    {
        $path = self::lockPath();
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /** هل قاعدة البيانات متصلة وتمّ تشغيل migrations؟ (لا نعرض أي تفاصيل خطأ للزائر) */
    public static function databaseReady(): bool
    {
        try {
            DB::connection()->getPdo();

            return Schema::hasTable('users') && Schema::hasTable('store_settings');
        } catch (Throwable) {
            return false;
        }
    }

    public static function tokenConfigured(): bool
    {
        return (string) config('store.install_token') !== '';
    }

    /** في production لا نسمح بالتثبيت دون رمز تثبيت. */
    public static function tokenRequired(): bool
    {
        return self::tokenConfigured() || app()->isProduction();
    }

    public static function tokenIsValid(?string $given): bool
    {
        if (! self::tokenConfigured()) {
            return ! app()->isProduction();
        }

        return is_string($given) && hash_equals((string) config('store.install_token'), $given);
    }
}
