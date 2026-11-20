<?php

namespace App\Services;

use App\Models\ShippingZone;
use App\Models\StoreSetting;
use Illuminate\Support\Collection;

/** تكلفة الشحن: حسب المنطقة إن عرّف المالك مناطق، وإلا الرسم الثابت من الإعدادات. */
class ShippingCalculator
{
    /** @return Collection<int,ShippingZone> */
    public static function zones(): Collection
    {
        return ShippingZone::query()->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get();
    }

    /** @return array{min:float,max:float,uses_zones:bool} */
    public static function range(StoreSetting $settings): array
    {
        $zones = self::zones();
        if ($zones->isEmpty()) {
            $fee = (float) $settings->shipping_fee;

            return ['min' => $fee, 'max' => $fee, 'uses_zones' => false];
        }

        return ['min' => (float) $zones->min('fee'), 'max' => (float) $zones->max('fee'), 'uses_zones' => true];
    }
}
