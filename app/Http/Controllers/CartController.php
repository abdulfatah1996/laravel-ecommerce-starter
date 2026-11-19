<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\Cart;
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

        return view('cart', [
            'lines' => $this->cart->lines(),
            'subtotal' => $subtotal,
            'shipping' => (float) $settings->shipping_fee,
            'total' => round($subtotal + (float) $settings->shipping_fee, 2),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse
    {
        $max = (int) config('store.max_cart_quantity', 20);
        $data = $request->validate(['quantity' => ['nullable', 'integer', 'min:1', "max:{$max}"]]);

        if (! $product->is_active || ! $product->isInStock()) {
            return back()->with('error', 'هذا المنتج غير متوفر حاليًا.');
        }

        $this->cart->add($product->id, (int) ($data['quantity'] ?? 1));

        return back()->with('status', 'تمت إضافة المنتج إلى السلة.');
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $max = (int) config('store.max_cart_quantity', 20);
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', "max:{$max}"]]);

        $this->cart->set($product->id, (int) $data['quantity']);

        return redirect()->route('cart.index');
    }

    public function remove(Product $product): RedirectResponse
    {
        $this->cart->remove($product->id);

        return redirect()->route('cart.index')->with('status', 'تمت إزالة المنتج من السلة.');
    }
}
