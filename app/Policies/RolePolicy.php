<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/**
 * سياسة أساسية تعتمد مستوى الدور (staff < manager < admin).
 * تُعرّف كل الدوال التي تستدعيها Filament كي لا يُرفض شيء بسبب غياب دالة.
 */
abstract class RolePolicy
{
    protected UserRole $read = UserRole::Staff;

    protected UserRole $edit = UserRole::Staff;

    protected UserRole $create = UserRole::Manager;

    protected UserRole $delete = UserRole::Manager;

    public function viewAny(User $user): bool
    {
        return $user->hasRoleAtLeast($this->read);
    }

    public function view(User $user, mixed $model = null): bool
    {
        return $user->hasRoleAtLeast($this->read);
    }

    public function create(User $user): bool
    {
        return $user->hasRoleAtLeast($this->create);
    }

    public function update(User $user, mixed $model = null): bool
    {
        return $user->hasRoleAtLeast($this->edit);
    }

    public function delete(User $user, mixed $model = null): bool
    {
        return $user->hasRoleAtLeast($this->delete);
    }

    public function deleteAny(User $user): bool
    {
        return $user->hasRoleAtLeast($this->delete);
    }

    public function replicate(User $user, mixed $model = null): bool
    {
        return $this->create($user);
    }

    public function reorder(User $user): bool
    {
        return $user->hasRoleAtLeast($this->edit);
    }

    public function restore(User $user, mixed $model = null): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, mixed $model = null): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }
}
