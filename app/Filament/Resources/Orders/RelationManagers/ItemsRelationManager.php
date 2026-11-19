<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Models\StoreSetting;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/** بنود الطلب — للقراءة فقط (لقطة من الأسعار وقت الطلب). */
class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    protected static ?string $title = 'بنود الطلب';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        $money = fn ($state) => StoreSetting::current()->money($state);

        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')->label('المنتج'),
                TextColumn::make('sku')->label('الرمز'),
                TextColumn::make('unit_price')->label('سعر الوحدة')->formatStateUsing($money),
                TextColumn::make('quantity')->label('الكمية'),
                TextColumn::make('line_total')->label('الإجمالي')->formatStateUsing($money),
            ])
            ->paginated(false);
    }
}
