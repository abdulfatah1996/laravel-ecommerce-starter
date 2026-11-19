<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use App\Support\Slug;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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
use Filament\Tables\Table;
use UnitEnum;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';

    protected static string|UnitEnum|null $navigationGroup = 'الكتالوج';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'تصنيف';

    protected static ?string $pluralModelLabel = 'التصنيفات';

    protected static ?string $navigationLabel = 'التصنيفات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('name')
                    ->label('الاسم')
                    ->required()
                    ->maxLength(150)
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
                Textarea::make('description')->label('الوصف')->rows(3)->maxLength(1000),
                FileUpload::make('image')
                    ->label('الصورة')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->maxSize(2048)
                    ->disk('public')
                    ->directory('categories')
                    ->visibility('public'),
                TextInput::make('sort_order')->label('الترتيب')->numeric()->integer()->minValue(0)->default(0),
                Toggle::make('is_active')->label('ظاهر في المتجر')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')->label('الصورة')->disk('public')->square(),
                TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('products_count')->label('عدد المنتجات')->counts('products'),
                TextColumn::make('sort_order')->label('الترتيب')->sortable(),
                ToggleColumn::make('is_active')->label('ظاهر'),
            ])
            ->defaultSort('sort_order')
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
