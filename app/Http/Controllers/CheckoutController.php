<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Payments\PaymentRegistry;
use App\Services\Cart;
use App\Services\OrderPlacer;
use App\Services\ShippingCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    private const TOKEN_KEY = 'checkout.token';

    private const LAST_ORDER_KEY = 'checkout.last_order_id';

    public function __construct(
        private readonly Cart $cart,
        private readonly PaymentRegistry $payments,
    ) {}

    public function show(Request $request): View|RedirectResponse
    {
        if ($this->cart->isEmpty() || $this->cart->lines()->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'سلتك فارغة.');
        }

        $settings = StoreSetting::current();
        $subtotal = $this->cart->subtotal();

        if (! $request->session()->has(self::TOKEN_KEY)) {
            $request->session()->put(self::TOKEN_KEY, (string) Str::uuid());
        }

        return view('checkout', [
            'lines' => $this->cart->lines(),
            'subtotal' => $subtotal,
            'zones' => ShippingCalculator::zones(),
            'baseShipping' => (float) $settings->shipping_fee,
            'user' => $request->user(),
            'methods' => $this->payments->enabled($settings),
            'settings' => $settings,
            'token' => $request->session()->get(self::TOKEN_KEY),
        ]);
    }

    public function store(CheckoutRequest $request, OrderPlacer $placer): RedirectResponse
    {
        $sessionToken = $request->session()->get(self::TOKEN_KEY);

        if (! is_string($sessionToken) || ! hash_equals($sessionToken, (string) $request->input('checkout_token'))) {
            // انتهت الجلسة أو الرمز غير صالح: لا ننشئ طلبًا، ونطلب إعادة المحاولة.
            $request->session()->forget(self::TOKEN_KEY);

            return redirect()->route('checkout.show')->with('error', 'انتهت صلاحية النموذج، يرجى المحاولة مرة أخرى.');
        }

        try {
            $order = $placer->place(
                $request->safe()->only(['name', 'phone', 'email', 'city', 'address', 'notes', 'payment_method', 'shipping_zone_id']),
                $this->cart->items(),
                $sessionToken,
                StoreSetting::current(),
                $request->user()?->id,
            );
        } catch (CheckoutException $e) {
            return redirect()->route('cart.index')->with('error', $e->getMessage());
        }

        $this->cart->clear();
        $request->session()->forget(self::TOKEN_KEY);
        $request->session()->put(self::LAST_ORDER_KEY, $order->id);

        // خطاف بوابات الدفع المستقبلية (الطرق الحالية ترجع null).
        $redirect = $this->payments->find($order->payment_method)?->afterOrderPlaced($order);

        return $redirect ?? redirect()->route('checkout.success');
    }

    /** صفحة النجاح تعرض فقط آخر طلب أُنشئ في هذه الجلسة — لا يمكن فتح طلب شخص آخر. */
    public function success(Request $request): View|RedirectResponse
    {
        $orderId = $request->session()->get(self::LAST_ORDER_KEY);
        $order = $orderId ? Order::query()->with('items')->find($orderId) : null;

        if ($order === null) {
            return redirect()->route('home');
        }

        $settings = StoreSetting::current();

        return view('order-success', [
            'order' => $order,
            'method' => $this->payments->find($order->payment_method),
            'settings' => $settings,
        ]);
    }
}
