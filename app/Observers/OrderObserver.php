<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/** يعيد المخزون عند إلغاء الطلب، ويخصمه مجددًا إن أُعيد تفعيل طلب ملغي. */
class OrderObserver
{
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $old = $order->getOriginal('status');
        $old = $old instanceof OrderStatus ? $old : OrderStatus::tryFrom((string) $old);
        $new = $order->status;

        if ($new === OrderStatus::Cancelled && $old !== OrderStatus::Cancelled) {
            $this->adjustStock($order, +1);
        } elseif ($old === OrderStatus::Cancelled && $new !== OrderStatus::Cancelled) {
            $this->adjustStock($order, -1);
        }
    }

    private function adjustStock(Order $order, int $direction): void
    {
        DB::transaction(function () use ($order, $direction) {
            foreach ($order->items()->whereNotNull('product_id')->get() as $item) {
                $product = Product::query()->lockForUpdate()->find($item->product_id);
                if ($product === null) {
                    continue;
                }
                $product->update(['stock' => max(0, $product->stock + ($direction * $item->quantity))]);
            }
        });
    }
}
