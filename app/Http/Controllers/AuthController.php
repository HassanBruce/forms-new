<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin() { return view('auth.login'); }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($cred + ['status' => 'active'], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These details don’t match an active account.'])->onlyInput('email');
        }
        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'));
    }

    public function showRegister() { return view('auth.register'); }

    /** SaaS sign-up: creates the agency (parent company), a first sub-brand and the admin. */
    public function register(Request $request)
    {
        $d = $request->validate([
            'company' => 'required|string|max:120',
            'brand_code' => 'required|alpha_num|max:10',
            'name' => 'required|string|max:120',
            'email' => 'required|email|max:190|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'currency' => 'required|in:GBP,USD,EUR,PKR,SAR',
        ]);

        $user = DB::transaction(function () use ($d) {
            $base = Str::slug($d['company']) ?: 'agency';
            $slug = $base; $i = 2;
            while (Company::where('slug', $slug)->exists()) $slug = $base.'-'.$i++;
            $company = Company::create([
                'name' => $d['company'], 'slug' => $slug, 'currency' => $d['currency'],
                'booking_prefix' => strtoupper(substr($d['brand_code'], 0, 4)), 'trial_ends_at' => now()->addDays(14),
            ]);
            $brand = new Brand(['name' => $d['company'], 'code' => strtoupper($d['brand_code']), 'email' => $d['email']]);
            $brand->company_id = $company->id;
            $brand->save();
            return User::create([
                'company_id' => $company->id, 'brand_id' => $brand->id, 'role' => 'admin',
                'name' => $d['name'], 'email' => $d['email'], 'password' => $d['password'],
            ]);
        });

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->route('dashboard')->with('ok', 'Welcome to Tripdesk! Your 14-day trial has started.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
