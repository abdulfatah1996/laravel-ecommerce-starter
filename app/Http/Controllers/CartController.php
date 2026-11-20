<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\Cart;
use App\Services\ShippingCalculator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly Cart $cart) {}

    public function index(): View
    {
        $settings = StoreSetting::current();
        $subtotal = $this->cart->subtotal();
        $shipping = ShippingCalculator::range($settings);

        return view('cart', [
            'lines' => $this->cart->lines(),
            'subtotal' => $subtotal,
            'shippingRange' => $shipping,
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $max = (int) config('store.max_cart_quantity', 20);
        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', "max:{$max}"],
            'variant_id' => ['nullable', 'integer'],
        ]);

        if (! $product->is_active) {
            return back()->with('error', 'هذا المنتج غير متوفر حاليًا.');
        }

        $variantId = null;
        if ($product->hasVariants()) {
            $variant = $product->variants->where('is_active', true)->firstWhere('id', (int) ($data['variant_id'] ?? 0));
            if ($variant === null) {
                return back()->with('error', 'اختر '.($product->option_name ?: 'الخيار').' أولًا.');
            }
            if ($variant->stock < 1) {
                return back()->with('error', 'هذا الخيار غير متوفر حاليًا.');
            }
            $variantId = $variant->id;
        } elseif (! $product->isInStock()) {
            return back()->with('error', 'هذا المنتج غير متوفر حاليًا.');
        }

        $this->cart->add($product->id, (int) ($data['quantity'] ?? 1), $variantId);

        return back()->with('status', 'تمت إضافة المنتج إلى السلة.');
    }

    public function update(Request $request, string $key): RedirectResponse
    {
        $max = (int) config('store.max_cart_quantity', 20);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', "max:{$max}"]]);

        $this->cart->set($key, (int) $data['quantity']);

        return redirect()->route('cart.index');
    }

    public function remove(string $key): RedirectResponse
    {
        $this->cart->remove($key);

        return redirect()->route('cart.index')->with('status', 'تمت إزالة المنتج من السلة.');
    }
}
