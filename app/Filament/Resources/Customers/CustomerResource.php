<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'المبيعات';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'عميل';

    protected static ?string $pluralModelLabel = 'العملاء';

    protected static ?string $navigationLabel = 'العملاء';

    public static function canCreate(): bool
    {
        return false; // العملاء يُنشأون تلقائيًا عند الطلب
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')->label('الاسم')->required()->maxLength(120),
                TextInput::make('phone')->label('الهاتف')->required()->maxLength(40)->unique(ignoreRecord: true),
                TextInput::make('email')->label('البريد الإلكتروني')->email()->maxLength(190),
                TextInput::make('city')->label('المدينة')->maxLength(100),
                TextInput::make('address')->label('العنوان')->maxLength(255)->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('phone')->label('الهاتف')->searchable(),
                TextColumn::make('email')->label('البريد')->searchable(),
                TextColumn::make('city')->label('المدينة'),
                TextColumn::make('orders_count')->label('عدد الطلبات')->counts('orders')->sortable(),
                TextColumn::make('created_at')->label('تاريخ التسجيل')->dateTime('Y-m-d')->sortable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }
}
