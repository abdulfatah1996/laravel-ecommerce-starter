<?php

namespace App\Policies;

use App\Enums\UserRole;

/** الصفحات الثابتة: تعديلها لمدير المتجر فأعلى. */
class PagePolicy extends RolePolicy
{
    protected UserRole $edit = UserRole::Manager;
}
