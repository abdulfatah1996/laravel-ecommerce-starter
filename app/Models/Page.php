<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = ['slug', 'title', 'body', 'is_published'];

    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    /** المحتوى مكتوب Markdown ويُعرض بعد إزالة أي HTML خام (حماية من XSS). */
    public function renderedBody(): string
    {
        return Str::markdown((string) $this->body, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * صفحات افتراضية عامة. محتواها نصوص إرشادية عامة وليست استشارة قانونية؛
     * يجب أن يراجعها صاحب المتجر ويعدّلها قبل الإطلاق.
     *
     * @return array<string,array{title:string,body:string}>
     */
    public static function defaults(): array
    {
        return [
            'about' => [
                'title' => 'من نحن',
                'body' => "عدّل هذا النص من لوحة الإدارة (الصفحات).\n\nاكتب هنا نبذة عن متجرك وقصته وما يميّز منتجاتك.",
            ],
            'contact' => [
                'title' => 'اتصل بنا',
                'body' => 'يسعدنا تواصلك معنا عبر وسائل الاتصال الموضحة أدناه.',
            ],
            'privacy' => [
                'title' => 'سياسة الخصوصية',
                'body' => "نص إرشادي عام — يجب مراجعته وتعديله ليناسب متجرك والقوانين المحلية.\n\n"
                    ."- نجمع الاسم ورقم الهاتف والعنوان لغرض تنفيذ الطلبات فقط.\n"
                    ."- لا نبيع بيانات الزبائن لأطراف أخرى.\n"
                    ."- يمكنك التواصل معنا لطلب تعديل بياناتك أو حذفها.\n",
            ],
            'shipping' => [
                'title' => 'الشحن والتوصيل',
                'body' => "نص إرشادي عام — عدّله من لوحة الإدارة.\n\n- مناطق التوصيل وأسعارها تظهر عند إتمام الطلب.\n- مدة التوصيل المتوقعة: (حدّد المدة).\n- الدفع عند الاستلام متاح.\n",
            ],
            'returns' => [
                'title' => 'سياسة الاستبدال والاسترجاع',
                'body' => "نص إرشادي عام — يجب مراجعته وتعديله ليناسب متجرك والقوانين المحلية.\n\n"
                    ."- مدة الاستبدال أو الاسترجاع: (حدّد المدة).\n"
                    ."- شروط المنتج المقبول للإرجاع: (حدّد الشروط).\n"
                    ."- طريقة التواصل لطلب الإرجاع: (حدّد الطريقة).\n",
            ],
        ];
    }
}
