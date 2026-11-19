<?php

use App\Payments\CashOnDelivery;
use App\Payments\ManualTransfer;

return [
    /*
    | ملف القفل الذي يمنع إعادة تشغيل صفحة /install بعد اكتمال التثبيت.
    */
    'install_lock_path' => env('STORE_INSTALL_LOCK', storage_path('app/installed.lock')),

    /*
    | رمز التثبيت الأول. مطلوب في بيئة production (انظر INSTALL_TOKEN في .env.example).
    */
    'install_token' => env('INSTALL_TOKEN'),

    /*
    | العملات المتاحة (الرمز => [الاسم، الرمز المعروض]). أضف ما تحتاجه.
    */
    'currencies' => [
        'ILS' => ['name' => 'شيكل إسرائيلي', 'symbol' => '₪'],
        'JOD' => ['name' => 'دينار أردني', 'symbol' => 'د.أ'],
        'USD' => ['name' => 'دولار أمريكي', 'symbol' => '$'],
        'EUR' => ['name' => 'يورو', 'symbol' => '€'],
        'SAR' => ['name' => 'ريال سعودي', 'symbol' => 'ر.س'],
        'AED' => ['name' => 'درهم إماراتي', 'symbol' => 'د.إ'],
        'EGP' => ['name' => 'جنيه مصري', 'symbol' => 'ج.م'],
        'KWD' => ['name' => 'دينار كويتي', 'symbol' => 'د.ك'],
        'QAR' => ['name' => 'ريال قطري', 'symbol' => 'ر.ق'],
        'TRY' => ['name' => 'ليرة تركية', 'symbol' => '₺'],
    ],

    /*
    | طرق الدفع المسجّلة. لإضافة بوابة دفع مستقبلًا: أنشئ صنفًا يطبّق
    | App\Payments\PaymentMethod وأضفه هنا (انظر docs/DATABASE.md وREADME).
    */
    'payment_methods' => [
        CashOnDelivery::class,
        ManualTransfer::class,
    ],

    'default_colors' => [
        'primary' => '#0f766e',
        'secondary' => '#e7e5e4',
        'accent' => '#f59e0b',
    ],

    'max_cart_quantity' => 20,
    'low_stock_threshold' => 5,
];
