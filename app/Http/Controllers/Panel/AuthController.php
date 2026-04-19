<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('panel.auth.login');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => ['required'],
            'password' => ['required'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!Auth::attempt(['phone' => $request->phone, 'password' => $request->password])) {
            return back()->withErrors(['phone' => 'Telefon numarası veya şifre hatalı.'])->withInput();
        }

        $request->session()->regenerate();
        return redirect()->route('panel.dashboard');
    }

    public function registerForm()
    {
        return view('panel.auth.register');
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6'],
            'gender' => ['required', 'in:male,female'],
        ], [
            'phone.unique' => 'Bu telefon numarası zaten kayıtlı.',
            'password.min' => 'Şifre en az 6 karakter olmalı.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('panel.setup')->with('success', 'Hesabınız oluşturuldu. Dükkanınızı kaydedin.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('panel.login');
    }

    // Dükkan kurulum formu (kayıt sonrası)
    public function setupForm()
    {
        $shop = Shop::where('owner_id', auth()->id())->first();
        if ($shop) {
            return redirect()->route('panel.dashboard');
        }
        return view('panel.auth.setup');
    }

    public function setup(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'city' => ['required', 'string'],
            'district' => ['required', 'string'],
            'lat' => ['required', 'numeric'],
            'lng' => ['required', 'numeric'],
            'gender_filter' => ['required', 'in:male,female,both'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Slug oluştur
        $slug = \Str::slug($request->name) . '-' . rand(1000, 9999);

        $shop = Shop::create([
            'owner_id' => auth()->id(),
            'name' => $request->name,
            'slug' => $slug,
            'address' => $request->address,
            'city' => $request->city,
            'district' => $request->district,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'gender_filter' => $request->gender_filter,
            'phone' => $request->phone,
        ]);

        // Varsayılan ayarları oluştur
        \App\Models\ShopSetting::create(['shop_id' => $shop->id]);
        \App\Models\NotificationSetting::create(['shop_id' => $shop->id]);
        \App\Models\QueueStatus::create(['shop_id' => $shop->id]);

        return redirect()->route('panel.dashboard')->with('success', 'Dükkanınız oluşturuldu!');
    }
}
