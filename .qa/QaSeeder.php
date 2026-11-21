<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Support\Slug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

/** بيانات معاينة للفحص البصري فقط (صور توضيحية، ليست منتجات حقيقية). */
class QaSeeder extends Seeder
{
    public function run(): void
    {
        File::ensureDirectoryExists(storage_path('app/public/products'));

        $cats = [];
        foreach (['إضاءة', 'ديكور', 'طاولة وتقديم', 'هدايا'] as $i => $name) {
            $cats[$name] = Category::updateOrCreate(
                ['slug' => Slug::make($name)],
                ['name' => $name, 'sort_order' => $i, 'is_active' => true]
            );
        }

        $long = 'مصباح طاولة سيراميك يدوي الصنع بتصميم عصري مع غطاء كتان طبيعي — المقاس الكبير';
        $desc = "قطعة مصنوعة يدويًا لتمنح ركنك دفئًا هادئًا.\nالارتفاع 42 سم والقاعدة 18 سم، ويعمل بمصباح E27 حتى 40 واط.\nننصح بمسحه بقطعة قماش جافة وعدم غمره بالماء.";

        // [الاسم، التصنيف، الصورة، السعر، قبل الخصم، المخزون، مميز]
        $catalog = [
            [$long, 'إضاءة', 'lamp', 129, 159, 8, true],
            ['مصباح أرضي «قنديل»', 'إضاءة', 'floor', 249, null, 7, true],
            ['ثريا سقف نحاسية', 'إضاءة', 'pendant', 319, 379, 3, true],
            ['شمعدان نحاسي ثلاثي', 'إضاءة', 'candle', 85, null, 12, false],
            ['وسادة كتان مطرّزة', 'ديكور', 'cushion', 45, null, 0, true],
            ['مزهرية سيراميك', 'ديكور', 'vase', 69.5, 89, 9, false],
            ['مرآة جدارية بيضاوية', 'ديكور', 'mirror', 189, null, 5, false],
            ['صينية تقديم خشب', 'طاولة وتقديم', 'tray', 95, null, 0, false],
            ['طقم هدية للضيافة', 'هدايا', 'tray', 120, 140, 6, false],
            ['شمعة معطّرة بالياسمين', 'هدايا', 'candle', 38, null, 20, false],
            ['مزهرية زجاج مدخّن', 'ديكور', 'vase', 55, null, 14, false],
            ['وسادة مخمل زيتية', 'ديكور', 'cushion', 52, 65, 9, false],
            ['مصباح مكتب صغير', 'إضاءة', 'lamp', 79, null, 11, false],
            ['صينية ضيافة نحاسية', 'هدايا', 'tray', 110, null, 4, false],
        ];

        $meta = [];
        foreach ($catalog as $i => [$name, $cat, $img, $price, $compare, $stock, $featured]) {
            $dest = "products/qa-{$img}.jpg";
            File::copy(base_path(".qa/images/{$img}.jpg"), storage_path('app/public/'.$dest));

            $product = Product::updateOrCreate(
                ['slug' => Slug::make($name).'-'.($i + 1)],
                [
                    'category_id' => $cats[$cat]->id, 'name' => $name, 'sku' => 'QA-'.($i + 1),
                    'description' => $desc, 'price' => $price, 'compare_at_price' => $compare, 'stock' => $stock,
                    'images' => [$dest], 'is_active' => true, 'is_featured' => $featured,
                ]
            );
            $meta[] = ['id' => $product->id, 'slug' => $product->slug, 'name' => $name, 'stock' => $stock];
        }

        File::put(storage_path('app/qa-products.json'), json_encode($meta, JSON_UNESCAPED_UNICODE));
    }
}
