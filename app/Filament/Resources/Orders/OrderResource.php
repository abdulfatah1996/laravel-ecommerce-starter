<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\RelationManagers\ItemsRelationManager;
use App\Models\Order;
use App\Models\StoreSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';

    protected static string|UnitEnum|null $navigationGroup = 'المبيعات';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'طلب';

    protected static ?string $pluralModelLabel = 'الطلبات';

    protected static ?string $navigationLabel = 'الطلبات';

    protected static ?string $recordTitleAttribute = 'number';

    public static function canCreate(): bool
    {
        return false; // الطلبات تأتي من المتجر فقط
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Order::query()->where('status', OrderStatus::Pending->value)->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الحالة')->schema([
                Select::make('status')->label('حالة الطلب')->options(OrderStatus::options())->required()->native(false),
                Select::make('payment_status')->label('حالة الدفع')->options(PaymentStatus::options())->required()->native(false),
            ])->columns(2),

            Section::make('بيانات الطلب (للقراءة فقط)')->schema([
                TextInput::make('number')->label('رقم الطلب')->disabled(),
                TextInput::make('payment_method')->label('طريقة الدفع')->disabled(),
                TextInput::make('customer_name')->label('الاسم')->disabled(),
                TextInput::make('customer_phone')->label('الهاتف')->disabled(),
                TextInput::make('customer_email')->label('البريد')->disabled(),
                TextInput::make('city')->label('المدينة')->disabled(),
                TextInput::make('address')->label('العنوان')->disabled()->columnSpanFull(),
                Textarea::make('notes')->label('ملاحظات الزبون')->disabled()->columnSpanFull(),
                TextInput::make('subtotal')->label('المجموع الفرعي')->disabled(),
                TextInput::make('shipping_zone')->label('منطقة الشحن')->disabled(),
                TextInput::make('shipping_fee')->label('الشحن')->disabled(),
                TextInput::make('total')->label('الإجمالي')->disabled(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')->label('رقم الطلب')->searchable()->copyable(),
                TextColumn::make('customer_name')->label('الزبون')->searchable(),
                TextColumn::make('customer_phone')->label('الهاتف')->searchable(),
                TextColumn::make('total')
                    ->label('الإجمالي')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => StoreSetting::current()->money($state)),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (OrderStatus $state) => $state->label())
                    ->color(fn (OrderStatus $state) => $state->color()),
                TextColumn::make('payment_status')
                    ->label('الدفع')
                    ->badge()
                    ->formatStateUsing(fn (PaymentStatus $state) => $state->label())
                    ->color(fn (PaymentStatus $state) => $state->color()),
                TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(OrderStatus::options()),
                SelectFilter::make('payment_status')->label('حالة الدفع')->options(PaymentStatus::options()),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make()->label('عرض / تعديل')]);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }
}
