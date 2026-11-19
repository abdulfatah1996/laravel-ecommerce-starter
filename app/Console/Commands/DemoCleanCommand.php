<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoCleanCommand extends Command
{
    protected $signature = 'store:demo-clean {--force : تنفيذ بدون سؤال تأكيد}';

    protected $description = 'يحذف البيانات التجريبية فقط (المنتجات والتصنيفات والعملاء والطلبات الموسومة is_demo). نفّذه قبل تسليم المتجر.';

    public function handle(): int
    {
        $counts = [
            'الطلبات' => Order::where('is_demo', true)->count(),
            'العملاء' => Customer::where('is_demo', true)->count(),
            'المنتجات' => Product::where('is_demo', true)->count(),
            'التصنيفات' => Category::where('is_demo', true)->count(),
        ];

        foreach ($counts as $label => $count) {
            $this->line("{$label}: {$count}");
        }

        if (array_sum($counts) === 0) {
            $this->info('لا توجد بيانات تجريبية.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('سيتم حذف هذه البيانات نهائيًا. هل أنت متأكد؟')) {
            return self::FAILURE;
        }

        DB::transaction(function () {
            Order::where('is_demo', true)->get()->each->delete();
            Customer::where('is_demo', true)->delete();
            Product::where('is_demo', true)->delete();
            Category::where('is_demo', true)->delete();
        });

        $this->info('تم حذف البيانات التجريبية.');

        return self::SUCCESS;
    }
}
