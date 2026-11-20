<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Support\Slug;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shopping-bag';

    protected static string|UnitEnum|null $navigationGroup = 'الكتالوج';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'المنتجات';

    protected static ?string $navigationLabel = 'المنتجات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('المعلومات الأساسية')->schema([
                TextInput::make('name')
                    ->label('اسم المنتج')
                    ->required()
                    ->maxLength(190)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation) {
                        if ($operation === 'create') {
                            $set('slug', Slug::make($state));
                        }
                    }),
                TextInput::make('slug')
                    ->label('الرابط (slug)')
                    ->required()
                    ->maxLength(190)
                    ->regex('/^[\p{L}\p{N}\-]+$/u')
                    ->unique(ignoreRecord: true),
                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload(),
                TextInput::make('sku')->label('رمز المنتج (SKU)')->maxLength(64)->unique(ignoreRecord: true),
                Textarea::make('description')->label('الوصف')->rows(5)->maxLength(5000)->columnSpanFull(),
            ])->columns(2),

            Section::make('خيارات المنتج (مقاس / لون)')
                ->description('اختياري. إن أضفت خيارات يصبح المخزون لكل خيار، ويمكن لكل خيار سعر خاص (اتركه فارغًا لاستخدام سعر المنتج).')
                ->schema([
                    TextInput::make('option_name')->label('اسم الخيار (مثال: المقاس أو اللون)')->maxLength(40),
                    Repeater::make('variants')
                        ->label('الخيارات')
                        ->relationship()
                        ->schema([
                            TextInput::make('label')->label('الخيار')->required()->maxLength(80),
                            TextInput::make('price')->label('السعر (اختياري)')->numeric()->minValue(0)->maxValue(9999999),
                            TextInput::make('stock')->label('المخزون')->required()->numeric()->integer()->minValue(0)->default(0),
                            TextInput::make('sku')->label('رمز (SKU)')->maxLength(64),
                            Toggle::make('is_active')->label('مفعّل')->default(true),
                        ])
                        ->columns(5)
                        ->orderColumn('sort_order')
                        ->reorderable()
                        ->addActionLabel('إضافة خيار')
                        ->defaultItems(0)
                        ->collapsible(),
                ]),

            Section::make('السعر والمخزون')->schema([
                TextInput::make('price')->label('السعر')->required()->numeric()->minValue(0)->maxValue(9999999),
                TextInput::make('compare_at_price')->label('السعر قبل الخصم (اختياري)')->numeric()->minValue(0)->maxValue(9999999),
                TextInput::make('stock')->label('الكمية في المخزون (لمنتج بلا خيارات)')->required()->numeric()->integer()->minValue(0)->default(0),
            ])->columns(3),

            Section::make('الصور')->schema([
                FileUpload::make('images')
                    ->label('صور المنتج (الأولى هي الرئيسية)')
                    ->image()
                    ->multiple()
                    ->reorderable()
                    ->maxFiles(6)
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public'),
            ]),

            Section::make('التوصيل والعرض')->schema([
                TextInput::make('delivery_note')->label('ملاحظة توصيل لهذا المنتج (اختياري)')->maxLength(255),
                Select::make('preview_3d')->label('عرض ثلاثي الأبعاد')->options(['lamp' => 'مصباح (قاعدة وغطاء)'])->placeholder('بدون')->native(false),
            ])->columns(2),

            Section::make('الظهور')->schema([
                Toggle::make('is_active')->label('ظاهر في المتجر')->default(true),
                Toggle::make('is_featured')->label('منتج مميز (يظهر في الرئيسية)'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('images')->label('الصورة')->disk('public')->limit(1)->square(),
                TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('category.name')->label('التصنيف')->sortable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => StoreSetting::current()->money($state)),
                TextColumn::make('stock')
                    ->label('المخزون')
                    ->sortable()
                    ->badge()
                    ->state(fn (Product $record): int => $record->hasVariants() ? (int) $record->variants()->where('is_active', true)->sum('stock') : (int) $record->stock)
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= (int) config('store.low_stock_threshold') => 'warning',
                        default => 'success',
                    }),
                ToggleColumn::make('is_active')->label('ظاهر'),
            ])
            ->filters([
                SelectFilter::make('category_id')->label('التصنيف')->relationship('category', 'name'),
                TernaryFilter::make('is_active')->label('الظهور'),
                Filter::make('low_stock')
                    ->label('مخزون منخفض')
                    ->query(fn (Builder $query) => $query->where('stock', '<=', (int) config('store.low_stock_threshold'))),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
