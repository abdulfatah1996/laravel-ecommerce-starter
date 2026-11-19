<?php

namespace Tests;

use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected string $lockPath;

    protected function setUp(): void
    {
        parent::setUp();

        // ملف قفل مؤقت لكل اختبار كي لا يتأثر storage/app/installed.lock الحقيقي.
        $this->lockPath = sys_get_temp_dir().'/store-test-'.uniqid('', true).'.lock';
        config(['store.install_lock_path' => $this->lockPath]);

        // لا نحتاج ملفات Vite المبنية داخل الاختبارات.
        $this->withoutVite();
    }

    protected function tearDown(): void
    {
        if (is_file($this->lockPath)) {
            @unlink($this->lockPath);
        }

        parent::tearDown();
    }

    /** يجعل المتجر "مثبّتًا" (صف إعدادات مع installed_at). */
    protected function installStore(array $overrides = []): StoreSetting
    {
        return StoreSetting::create(array_merge(StoreSetting::defaults(), [
            'name' => 'متجر الاختبار',
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'installed_at' => now(),
        ], $overrides));
    }
}
