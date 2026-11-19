<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'role' => UserRole::class,
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active && $this->role instanceof UserRole;
    }

    /** هل مستوى صلاحية المستخدم يساوي أو يفوق المستوى المطلوب؟ */
    public function hasRoleAtLeast(UserRole $role): bool
    {
        return $this->is_active
            && $this->role instanceof UserRole
            && $this->role->level() >= $role->level();
    }

    public function isAdmin(): bool
    {
        return $this->hasRoleAtLeast(UserRole::Admin);
    }
}
