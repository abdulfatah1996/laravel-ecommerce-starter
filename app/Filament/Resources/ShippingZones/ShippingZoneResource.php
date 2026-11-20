<?php

namespace App\Filament\Resources\ShippingZones;

use App\Filament\Resources\ShippingZones\Pages\CreateShippingZone;
use App\Filament\Resources\ShippingZones\Pages\EditShippingZone;
use App\Filament\Resources\ShippingZones\Pages\ListShippingZones;
use App\Models\ShippingZone;
use App\Models\StoreSetting;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

/** مناطق الشحن وأسعارها. إن لم تُعرَّف أي منطقة فعّالة يُستخدم «رسوم الشحن الثابتة» في إعدادات المتجر. */
class ShippingZoneResource extends Resource
{
    protected static ?string $model = ShippingZone::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'المبيعات';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'منطقة شحن';

    protected static ?string $pluralModelLabel = 'مناطق وأسعار الشحن';

    protected static ?string $navigationLabel = 'مناطق الشحن';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('اسم المنطقة')->required()->maxLength(100),
            TextInput::make('fee')->label('سعر الشحن (0 = مجاني)')->required()->numeric()->minValue(0)->maxValue(99999)->default(0),
            TextInput::make('sort_order')->label('الترتيب')->numeric()->integer()->default(0),
            Toggle::make('is_active')->label('مفعّلة')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('المنطقة')->searchable(),
                TextColumn::make('fee')->label('سعر الشحن')->formatStateUsing(fn ($state) => (float) $state > 0 ? StoreSetting::current()->money($state) : 'مجاني')->sortable(),
                ToggleColumn::make('is_active')->label('مفعّلة'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListShippingZones::route('/'),
            'create' => CreateShippingZone::route('/create'),
            'edit' => EditShippingZone::route('/{record}/edit'),
        ];
    }
}
