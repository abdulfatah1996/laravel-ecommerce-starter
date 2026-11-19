<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->installStore();
    }

    public function test_guests_are_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_open_dashboard_and_users(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/admin/store-settings')->assertOk();
    }

    public function test_staff_cannot_manage_users_or_settings(): void
    {
        $staff = User::factory()->role(UserRole::Staff)->create();

        $this->actingAs($staff)->get('/admin/users')->assertForbidden();
        $this->actingAs($staff)->get('/admin/store-settings')->assertForbidden();
        $this->actingAs($staff)->get('/admin/orders')->assertOk();
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        $inactive = User::factory()->role(UserRole::Admin)->inactive()->create();

        $this->actingAs($inactive)->get('/admin')->assertForbidden();
    }

    public function test_policies_by_role(): void
    {
        $admin = User::factory()->role(UserRole::Admin)->create();
        $manager = User::factory()->role(UserRole::Manager)->create();
        $staff = User::factory()->role(UserRole::Staff)->create();

        $this->assertTrue($staff->can('update', new Product));
        $this->assertFalse($staff->can('create', Product::class));
        $this->assertFalse($staff->can('delete', new Product));
        $this->assertTrue($manager->can('create', Product::class));
        $this->assertTrue($manager->can('delete', new Product));

        $this->assertFalse($admin->can('create', Order::class));
        $this->assertFalse($manager->can('delete', new Order));
        $this->assertTrue($admin->can('delete', new Order));

        $this->assertFalse($admin->can('delete', $admin), 'المدير لا يحذف نفسه');
        $this->assertFalse($manager->can('viewAny', User::class));
    }
}
