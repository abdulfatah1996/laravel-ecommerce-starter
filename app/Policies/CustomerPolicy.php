<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;

/** بيانات العملاء: الموظف يعرض فقط، المدير يعدّل، الحذف للمدير العام. */
class CustomerPolicy extends RolePolicy
{
    protected UserRole $edit = UserRole::Manager;

    protected UserRole $delete = UserRole::Admin;

    public function create(User $user): bool
    {
        return false; // العملاء يُنشأون تلقائيًا عند الطلب
    }
}
