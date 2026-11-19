<?php

namespace App\Models;

use App\Support\Colors;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StoreSetting extends Model
{
    public const CACHE_KEY = 'store.settings';

    protected $fillable = [
        'name', 'description', 'logo', 'email', 'phone', 'country', 'city', 'address',
        'currency_code', 'currency_symbol', 'timezone', 'locale',
        'primary_color', 'secondary_color', 'accent_color',
        'shipping_fee', 'cod_enabled', 'manual_payment_enabled',
        'manual_payment_label', 'manual_payment_instructions', 'installed_at',
    ];

    protected function casts(): array
    {
        return [
            'shipping_fee' => 'decimal:2',
            'cod_enabled' => 'boolean',
            'manual_payment_enabled' => 'boolean',
            'installed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget(self::CACHE_KEY);
        static::saved($forget);
        static::deleted($forget);
    }

    /** @return array<string,mixed> */
    public static function defaults(): array
    {
        return [
            'name' => (string) config('app.name', 'متجري'),
            'currency_code' => 'USD',
            'currency_symbol' => '$',
            'timezone' => (string) config('app.timezone', 'UTC'),
            'locale' => 'ar',
            'primary_color' => config('store.default_colors.primary'),
            'secondary_color' => config('store.default_colors.secondary'),
            'accent_color' => config('store.default_colors.accent'),
            'shipping_fee' => 0,
            'cod_enabled' => true,
            'manual_payment_enabled' => false,
        ];
    }

    /**
     * إعدادات المتجر الحالية (مخزّنة مؤقتًا). تعمل حتى قبل تجهيز قاعدة البيانات
     * (ترجع قيمًا افتراضية) كي لا تنكسر الصفحات أثناء التثبيت.
     */
    public static function current(): static
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached instanceof static) {
            return $cached;
        }

        try {
            $row = static::query()->first();
        } catch (Throwable) {
            return new static(static::defaults());
        }

        if ($row === null) {
            return new static(static::defaults());
        }

        Cache::forever(self::CACHE_KEY, $row);

        return $row;
    }

    public function isInstalled(): bool
    {
        return $this->exists && $this->installed_at !== null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public function onPrimary(): string
    {
        return Colors::onColor((string) $this->primary_color);
    }

    public function onAccent(): string
    {
        return Colors::onColor((string) $this->accent_color);
    }

    public function money(float|int|string|null $amount): string
    {
        return number_format((float) $amount, 2).' '.$this->currency_symbol;
    }

    public function fullAddress(): string
    {
        return collect([$this->country, $this->city, $this->address])->filter()->implode('، ');
    }
}
