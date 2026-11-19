<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;

/**
 * دفع يدوي (تحويل بنكي/محفظة…): التعليمات يكتبها صاحب المتجر من الإعدادات،
 * ويؤكد الدفع يدويًا من لوحة الإدارة (payment_status).
 */
class ManualTransfer implements PaymentMethod
{
    public function key(): string
    {
        return 'manual';
    }

    public function label(StoreSetting $settings): string
    {
        return $settings->manual_payment_label ?: 'دفع يدوي (تحويل)';
    }

    public function description(StoreSetting $settings): ?string
    {
        return $settings->manual_payment_instructions;
    }

    public function isEnabled(StoreSetting $settings): bool
    {
        return (bool) $settings->manual_payment_enabled;
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
