<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/** إدارة المستخدمين: للمدير العام فقط، ولا يستطيع حذف نفسه. */
class UserPolicy extends RolePolicy
{
    protected UserRole $read = UserRole::Admin;

    protected UserRole $edit = UserRole::Admin;

    protected UserRole $create = UserRole::Admin;

    protected UserRole $delete = UserRole::Admin;

    public function delete(User $user, mixed $model = null): bool
    {
        return $user->isAdmin() && ! ($model instanceof User && $model->is($user));
    }
}
