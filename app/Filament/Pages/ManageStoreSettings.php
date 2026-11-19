<?php

namespace App\Filament\Pages;

use App\Enums\UserRole;
use App\Models\StoreSetting;
use App\Support\Colors;
use BackedEnum;
use DateTimeZone;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

/**
 * إعدادات المتجر: الاسم والوصف والشعار والتواصل والعملة والألوان وطرق الدفع.
 * التعديل متاح لمدير المتجر فأعلى.
 */
class ManageStoreSettings extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'إعدادات المتجر';

    protected static ?string $title = 'إعدادات المتجر';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'store-settings';

    protected string $view = 'filament.pages.manage-store-settings';

    /** @var array<string,mixed>|null */
    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRoleAtLeast(UserRole::Manager) ?? false;
    }

    public function mount(): void
    {
        $settings = StoreSetting::query()->first();

        $this->form->fill($settings?->attributesToArray() ?? StoreSetting::defaults());
    }

    public function form(Schema $schema): Schema
    {
        $hex = '/^#[0-9a-fA-F]{6}$/';

        return $schema
            ->components([
                Section::make('معلومات المتجر')->schema([
                    TextInput::make('name')->label('اسم المتجر')->required()->maxLength(100),
                    TextInput::make('email')->label('البريد الإلكتروني')->email()->maxLength(190),
                    TextInput::make('phone')->label('الهاتف')->tel()->maxLength(40),
                    TextInput::make('country')->label('الدولة')->maxLength(100),
                    TextInput::make('city')->label('المدينة')->maxLength(100),
                    TextInput::make('address')->label('العنوان')->maxLength(255),
                    Textarea::make('description')->label('وصف المتجر')->rows(3)->maxLength(500)->columnSpanFull(),
                    FileUpload::make('logo')
                        ->label('الشعار')
                        ->image()
                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                        ->maxSize(2048)
                        ->disk('public')
                        ->directory('branding')
                        ->visibility('public')
                        ->columnSpanFull(),
                ])->columns(2),

                Section::make('العملة والمنطقة الزمنية')->schema([
                    Select::make('currency_code')
                        ->label('العملة')
                        ->options(collect(config('store.currencies'))->map(fn ($c) => $c['name'].' ('.$c['symbol'].')')->all())
                        ->required()
                        ->native(false),
                    TextInput::make('currency_symbol')->label('رمز العملة المعروض')->required()->maxLength(10),
                    Select::make('timezone')
                        ->label('المنطقة الزمنية')
                        ->options(array_combine(DateTimeZone::listIdentifiers(), DateTimeZone::listIdentifiers()))
                        ->searchable()
                        ->required()
                        ->helperText('يُنصح بعدم تغييرها بعد بدء استقبال الطلبات.'),
                    Select::make('locale')->label('اللغة')->options(['ar' => 'العربية (RTL)'])->required()->native(false),
                    TextInput::make('shipping_fee')->label('رسوم الشحن الثابتة')->numeric()->minValue(0)->required(),
                ])->columns(2),

                Section::make('ألوان المتجر')->schema([
                    ColorPicker::make('primary_color')->label('اللون الأساسي')->required()->regex($hex),
                    ColorPicker::make('secondary_color')->label('اللون المساعد')->required()->regex($hex),
                    ColorPicker::make('accent_color')->label('لون الإبراز')->required()->regex($hex),
                ])->columns(3),

                Section::make('طرق الدفع')
                    ->description('لا يوجد دفع إلكتروني حقيقي في هذا القالب؛ الدفع عند الاستلام أو الدفع اليدوي فقط.')
                    ->schema([
                        Toggle::make('cod_enabled')->label('الدفع عند الاستلام'),
                        Toggle::make('manual_payment_enabled')->label('الدفع اليدوي (تحويل بنكي / محفظة)')->live(),
                        TextInput::make('manual_payment_label')->label('اسم طريقة الدفع اليدوي')->maxLength(100),
                        Textarea::make('manual_payment_instructions')
                            ->label('تعليمات الدفع اليدوي (تظهر للزبون)')
                            ->rows(4)
                            ->maxLength(1000)
                            ->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        if (! ($data['cod_enabled'] ?? false) && ! ($data['manual_payment_enabled'] ?? false)) {
            Notification::make()->title('فعّل طريقة دفع واحدة على الأقل.')->danger()->send();

            return;
        }

        foreach (['primary_color', 'secondary_color', 'accent_color'] as $key) {
            if (! Colors::isHex($data[$key] ?? null)) {
                Notification::make()->title('صيغة اللون غير صحيحة.')->danger()->send();

                return;
            }
        }

        $settings = StoreSetting::query()->firstOrNew();
        $oldLogo = $settings->logo;

        $settings->fill(Arr::except($data, ['installed_at']))->save();

        if ($oldLogo && $oldLogo !== $settings->logo) {
            Storage::disk('public')->delete($oldLogo);
        }

        Notification::make()->title('تم حفظ الإعدادات.')->success()->send();
    }
}
