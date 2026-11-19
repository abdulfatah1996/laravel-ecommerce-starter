<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\StoreSetting;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    protected static ?string $heading = 'أحدث الطلبات';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Order::query()->latest('id')->limit(8))
            ->columns([
                TextColumn::make('number')->label('رقم الطلب'),
                TextColumn::make('customer_name')->label('الزبون'),
                TextColumn::make('total')->label('الإجمالي')->formatStateUsing(fn ($state) => StoreSetting::current()->money($state)),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => $state->color()),
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i'),
            ])
            ->paginated(false);
    }
}
