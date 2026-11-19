<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|UnitEnum|null $navigationGroup = 'المحتوى';

    protected static ?string $modelLabel = 'صفحة';

    protected static ?string $pluralModelLabel = 'الصفحات';

    protected static ?string $navigationLabel = 'الصفحات';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('slug')
                    ->label('الصفحة')
                    ->options([
                        'about' => 'من نحن',
                        'contact' => 'اتصل بنا',
                        'privacy' => 'سياسة الخصوصية',
                        'returns' => 'سياسة الاستبدال والاسترجاع',
                    ])
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->native(false),
                TextInput::make('title')->label('العنوان')->required()->maxLength(190),
                MarkdownEditor::make('body')
                    ->label('المحتوى (Markdown)')
                    ->helperText('يدعم العناوين والقوائم والروابط. أي HTML خام يُزال تلقائيًا.')
                    ->columnSpanFull(),
                Toggle::make('is_published')->label('منشورة')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('العنوان')->searchable(),
                TextColumn::make('slug')->label('الرابط'),
                IconColumn::make('is_published')->label('منشورة')->boolean(),
                TextColumn::make('updated_at')->label('آخر تعديل')->dateTime('Y-m-d H:i'),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
