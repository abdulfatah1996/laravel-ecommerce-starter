<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $settings = StoreSetting::current();
        $sales = fn () => Order::query()->where('status', '!=', OrderStatus::Cancelled->value);

        return [
            Stat::make('مبيعات اليوم', $settings->money($sales()->whereDate('created_at', today())->sum('total')))
                ->description('الطلبات غير الملغاة')
                ->color('success'),
            Stat::make('مبيعات هذا الشهر', $settings->money($sales()->where('created_at', '>=', now()->startOfMonth())->sum('total'))),
            Stat::make('طلبات بانتظار المعالجة', (string) Order::query()->where('status', OrderStatus::Pending->value)->count())
                ->color('warning'),
            Stat::make(
                'منتجات منخفضة المخزون',
                (string) Product::query()->active()->where('stock', '<=', (int) config('store.low_stock_threshold'))->count()
            )->color('danger'),
        ];
    }
}
