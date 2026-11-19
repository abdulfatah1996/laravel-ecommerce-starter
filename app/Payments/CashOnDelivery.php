<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;

class CashOnDelivery implements PaymentMethod
{
    public function key(): string
    {
        return 'cod';
    }

    public function label(StoreSetting $settings): string
    {
        return 'الدفع عند الاستلام';
    }

    public function description(StoreSetting $settings): ?string
    {
        return 'تدفع قيمة الطلب نقدًا عند وصوله إليك.';
    }

    public function isEnabled(StoreSetting $settings): bool
    {
        return (bool) $settings->cod_enabled;
    }

    public function initialPaymentStatus(): PaymentStatus
    {
        return PaymentStatus::Unpaid;
    }

    public function afterOrderPlaced(Order $order): ?RedirectResponse
    {
        return null;
    }
}
