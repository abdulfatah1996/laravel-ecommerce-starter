<?php

namespace App\Policies;

use App\Enums\UserRole;

/** مناطق الشحن: لمدير المتجر فأعلى. */
class ShippingZonePolicy extends RolePolicy
{
    protected UserRole $read = UserRole::Manager;

    protected UserRole $edit = UserRole::Manager;
}
