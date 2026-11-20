<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/** حساب الزبون: تسجيل/دخول/خروج ومراجعة طلباته. لا يمنح أي وصول للوحة الإدارة (الدور فارغ). */
class AccountController extends Controller
{
    public function loginForm(): View
    {
        return view('account.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'البريد أو كلمة المرور غير صحيحة.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('account.orders'));
    }

    public function registerForm(): View
    {
        return view('account.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
        $user->role = null; // زبون: بلا صلاحية للوحة الإدارة
        $user->is_active = true;
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('account.orders');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function orders(Request $request): View
    {
        $orders = Order::query()->where('user_id', $request->user()->id)->withCount('items')->latest('id')->paginate(10);

        return view('account.orders', ['orders' => $orders]);
    }

    public function show(Request $request, Order $order): View
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 404);

        return view('account.order', ['order' => $order->load('items')]);
    }
}
