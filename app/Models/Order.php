<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'number', 'checkout_token', 'customer_id', 'user_id', 'status', 'payment_method', 'payment_status',
        'subtotal', 'shipping_fee', 'shipping_zone', 'total', 'currency_code',
        'customer_name', 'customer_phone', 'customer_email', 'city', 'address', 'notes', 'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'shipping_fee' => 'decimal:2',
            'total' => 'decimal:2',
            'is_demo' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** رقم طلب فريد وسهل القراءة (بدون أحرف ملتبسة). */
    public static function generateNumber(): string
    {
        do {
            $number = 'ORD-'.now()->format('ymd').'-'.strtoupper(Str::random(5));
            $number = strtr($number, ['O' => 'X', '0' => '7', 'I' => 'Y', '1' => '8']);
        } while (static::query()->where('number', $number)->exists());

        return $number;
    }
}
