<?php

namespace App\Payments;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Http\RedirectResponse;

/**
 * عقد طريقة الدفع. نظام الطلبات لا يعرف تفاصيل الدفع، فلإضافة بوابة دفع مستقبلًا:
 *  1) أنشئ صنفًا يطبّق هذا العقد (وتتأكد أنه موثّق ومختبر مع مزوّد الدفع).
 *  2) أضفه إلى config/store.php => payment_methods.
 *  3) استخدم afterOrderPlaced لتحويل الزبون إلى صفحة الدفع، وأنشئ webhook يحدّث payment_status.
 * لا يوجد حاليًا أي بوابة دفع إلكتروني حقيقية — فقط الدفع عند الاستلام والدفع اليدوي.
 */
interface PaymentMethod
{
    /** مفتاح فريد يُخزَّن في orders.payment_method */
    public function key(): string;

    public function label(StoreSetting $settings): string;

    public function description(StoreSetting $settings): ?string;

    public function isEnabled(StoreSetting $settings): bool;

    public function initialPaymentStatus(): PaymentStatus;

    /** خطاف بعد إنشاء الطلب: ارجع RedirectResponse لتحويل الزبون (بوابات الدفع)، أو null. */
    public function afterOrderPlaced(Order $order): ?RedirectResponse;
}
