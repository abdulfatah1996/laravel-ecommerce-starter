<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/** الطلبات: لا تُنشأ من اللوحة (تأتي من المتجر)، الموظف يعدّل الحالة، الحذف للمدير فقط. */
class OrderPolicy extends RolePolicy
{
    protected UserRole $delete = UserRole::Admin;

    public function create(User $user): bool
    {
        return false;
    }

    public function replicate(User $user, mixed $model = null): bool
    {
        return false;
    }
}
