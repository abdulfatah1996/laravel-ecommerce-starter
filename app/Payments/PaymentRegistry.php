<?php

namespace App\Payments;

use App\Models\StoreSetting;

class PaymentRegistry
{
    /** @var array<string,PaymentMethod> */
    private array $methods = [];

    /** @param  iterable<PaymentMethod>  $methods */
    public function __construct(iterable $methods)
    {
        foreach ($methods as $method) {
            $this->methods[$method->key()] = $method;
        }
    }

    /** @return array<string,PaymentMethod> الطرق المفعّلة حاليًا في إعدادات المتجر */
    public function enabled(StoreSetting $settings): array
    {
        return array_filter($this->methods, fn (PaymentMethod $m) => $m->isEnabled($settings));
    }

    public function find(string $key): ?PaymentMethod
    {
        return $this->methods[$key] ?? null;
    }
}
