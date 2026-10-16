<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    public function edit(Request $request)
    {
        return view('settings.edit', ['company' => $request->user()->company, 'user' => $request->user()]);
    }

    public function update(Request $request)
    {
        $u = $request->user();
        if ($request->input('section') === 'company') {
            abort_unless($u->canManageCompany(), 403);
            $u->company->update($request->validate([
                'name' => 'required|string|max:120',
                'currency' => 'required|in:GBP,USD,EUR,PKR,SAR',
                'booking_prefix' => 'required|alpha|max:6',
            ]));
            return back()->with('ok', 'Company settings saved.');
        }
        $d = $request->validate([
            'name' => 'required|string|max:120',
            'current_password' => 'required_with:password|current_password',
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ]);
        $u->name = $d['name'];
        if (! empty($d['password'])) $u->password = $d['password'];
        $u->save();
        return back()->with('ok', 'Profile saved.');
    }
}
