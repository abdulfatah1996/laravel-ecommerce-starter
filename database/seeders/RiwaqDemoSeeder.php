<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ShippingZone;
use App\Models\StoreSetting;
use App\Support\Slug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * معاينة محلية: متجر «رِواق» (إضاءة وإكسسوارات منزلية) بصور توضيحية مرسومة بالكود (ليست صور منتجات حقيقية).
 * كل شيء موسوم is_demo ويُحذف بـ: php artisan store:demo-clean
 * التشغيل: php artisan db:seed --class=RiwaqDemoSeeder
 */
class RiwaqDemoSeeder extends Seeder
{
    public function run(): void
    {
        $settings = StoreSetting::query()->first() ?? new StoreSetting(array_merge(StoreSetting::defaults(), ['primary_color' => '#7C3F1D', 'secondary_color' => '#F3E9D8', 'accent_color' => '#E9A23B']));
        $settings->fill([
            'name' => $settings->exists ? $settings->name : 'رِواق',
            'tagline' => $settings->tagline ?: 'إضاءة دافئة لبيتك',
            'description' => $settings->description ?: 'مصابيح وإكسسوارات منزلية نختارها بعناية لتمنح كل ركن في بيتك دفئًا.',
            'currency_code' => 'ILS',
            'currency_symbol' => '₪',
            'whatsapp_number' => $settings->whatsapp_number ?: '970599000000',
            'delivery_info' => 'توصيل خلال 2–4 أيام عمل. الدفع عند الاستلام.',
            'installed_at' => $settings->installed_at ?? now(),
        ])->save();

        foreach ([['الضفة الغربية', 20], ['القدس والداخل', 30], ['غزة', 35]] as $i => [$name, $fee]) {
            ShippingZone::updateOrCreate(['name' => $name], ['fee' => $fee, 'sort_order' => $i, 'is_active' => true]);
        }

        $cats = [];
        foreach (['إضاءة' => 'lamp', 'ديكور' => 'vase', 'طاولة وتقديم' => 'tray'] as $name => $img) {
            $cats[$name] = Category::updateOrCreate(
                ['slug' => Slug::make($name)],
                ['name' => $name, 'sort_order' => count($cats), 'is_active' => true, 'is_demo' => true, 'image' => $this->image($img, 'categories')]
            );
        }

        // [الاسم، التصنيف، الصورة، السعر، قبل الخصم، المخزون، مميز، اسم الخيار، الخيارات [[تسمية، سعر|null، مخزون]], 3d]
        $catalog = [
            ['مصباح طاولة «سراج»', 'إضاءة', 'lamp', 129, 159, 0, true, 'المقاس', [['صغير', 89, 6], ['كبير', 129, 2]], 'lamp'],
            ['مصباح أرضي «قنديل»', 'إضاءة', 'floor', 249, null, 7, true, null, [], null],
            ['ثريا سقف نحاسية', 'إضاءة', 'pendant', 319, 379, 4, true, null, [], null],
            ['شمعدان نحاسي ثلاثي', 'إضاءة', 'candle', 85, null, 12, false, null, [], null],
            ['وسادة كتان مطرّزة', 'ديكور', 'cushion', 45, null, 0, true, 'اللون', [['قرميدي', null, 10], ['عاجي', null, 0], ['زيتي', null, 3]], null],
            ['مزهرية سيراميك', 'ديكور', 'vase', 69, 89, 9, false, null, [], null],
            ['مرآة جدارية بيضاوية', 'ديكور', 'mirror', 189, null, 5, false, null, [], null],
            ['صينية تقديم خشب', 'طاولة وتقديم', 'tray', 95, null, 0, false, null, [], null],
        ];

        foreach ($catalog as $i => [$name, $cat, $img, $price, $compare, $stock, $featured, $optName, $variants, $preview]) {
            $product = Product::updateOrCreate(
                ['slug' => 'riwaq-'.($i + 1)],
                [
                    'category_id' => $cats[$cat]->id,
                    'name' => $name,
                    'sku' => 'RW-'.str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                    'description' => "{$name} من مجموعة رِواق.\nتصميم بسيط يناسب غرفة المعيشة وغرفة النوم.",
                    'option_name' => $optName,
                    'preview_3d' => $preview,
                    'price' => $price,
                    'compare_at_price' => $compare,
                    'stock' => $stock,
                    'images' => [$this->image($img, 'products')],
                    'is_active' => true,
                    'is_featured' => $featured,
                    'is_demo' => true,
                ]
            );

            if ($i === 0) {
                $settings->update(['hero_product_id' => $product->id]);
            }

            $product->variants()->delete();
            foreach ($variants as $j => [$label, $vPrice, $vStock]) {
                $product->variants()->create(['label' => $label, 'price' => $vPrice, 'stock' => $vStock, 'sort_order' => $j, 'is_active' => true]);
            }
        }
    }

    private function image(string $name, string $dir): string
    {
        $target = "{$dir}/demo-{$name}.jpg";
        Storage::disk('public')->put($target, File::get(database_path("seeders/demo-images/{$name}.jpg")));

        return $target;
    }
}
