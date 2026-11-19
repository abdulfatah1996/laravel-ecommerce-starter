<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Page;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string,mixed> */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'store_name' => 'متجر تجريبي',
            'description' => 'وصف',
            'email' => 'shop@example.com',
            'phone' => '+970000000',
            'country' => 'فلسطين',
            'city' => 'غزة',
            'address' => 'شارع الاختبار',
            'currency_code' => 'ILS',
            'timezone' => 'Asia/Hebron',
            'locale' => 'ar',
            'owner_name' => 'المالك',
            'owner_email' => 'owner@example.com',
            'owner_password' => 'StrongPass123',
            'owner_password_confirmation' => 'StrongPass123',
            'primary_color' => '#112233',
            'secondary_color' => '#eeeeee',
            'accent_color' => '#ffaa00',
        ], $overrides);
    }

    public function test_storefront_redirects_to_install_before_setup(): void
    {
        $this->get('/')->assertRedirect('/install');
    }

    public function test_install_form_is_shown_before_setup(): void
    {
        $this->get('/install')->assertOk()->assertSee('تجهيز المتجر');
    }

    public function test_installation_creates_settings_admin_and_default_pages(): void
    {
        $this->post('/install', $this->payload())->assertRedirect('/admin');

        $settings = StoreSetting::query()->first();
        $this->assertSame('متجر تجريبي', $settings->name);
        $this->assertSame('ILS', $settings->currency_code);
        $this->assertSame('#112233', $settings->primary_color);
        $this->assertNotNull($settings->installed_at);

        $admin = User::query()->where('email', 'owner@example.com')->first();
        $this->assertSame(UserRole::Admin, $admin->role);
        $this->assertTrue($admin->is_active);

        $this->assertSame(4, Page::query()->count());
        $this->assertFileExists($this->lockPath);
    }

    public function test_install_page_is_disabled_after_installation(): void
    {
        $this->post('/install', $this->payload())->assertRedirect('/admin');

        $this->get('/install')->assertNotFound();
        $this->post('/install', $this->payload(['owner_email' => 'second@example.com']))->assertNotFound();

        $this->assertSame(1, User::query()->count());
    }

    public function test_install_is_blocked_when_lock_file_exists(): void
    {
        file_put_contents($this->lockPath, 'locked');

        $this->get('/install')->assertNotFound();
    }

    public function test_invalid_input_is_rejected_and_nothing_is_created(): void
    {
        $this->post('/install', $this->payload(['primary_color' => 'red', 'owner_password' => 'short', 'owner_password_confirmation' => 'short']))
            ->assertSessionHasErrors(['primary_color', 'owner_password']);

        $this->assertSame(0, User::query()->count());
        $this->assertFileDoesNotExist($this->lockPath);
    }

    public function test_install_token_is_enforced_when_configured(): void
    {
        config(['store.install_token' => 'secret-token-value']);

        $this->post('/install', $this->payload(['install_token' => 'wrong']))->assertForbidden();
        $this->assertSame(0, User::query()->count());

        $this->post('/install', $this->payload(['install_token' => 'secret-token-value']))->assertRedirect('/admin');
        $this->assertSame(1, User::query()->count());
    }
}
