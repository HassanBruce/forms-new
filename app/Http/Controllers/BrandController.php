<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $brands = Brand::withCount(['users', 'bookings'])->withSum('bookings', 'total_sale')
            ->when($u->hasRole('director'), fn ($q) => $q->whereKey($u->brand_id))->orderBy('name')->get();
        return view('brands.index', compact('brands'));
    }

    public function store(Request $request)
    {
        abort_unless($request->user()->canManageCompany(), 403);
        Brand::create($this->validated($request));
        return back()->with('ok', 'Sub-brand created.');
    }

    public function update(Request $request, Brand $brand)
    {
        $u = $request->user();
        abort_unless($u->canManageCompany() || ($u->hasRole('director') && $u->brand_id === $brand->id), 403);
        $brand->update($this->validated($request, $brand));
        return back()->with('ok', "{$brand->name} updated.");
    }

    private function validated(Request $r, ?Brand $b = null): array
    {
        $d = $r->validate([
            'name' => 'required|string|max:120',
            'code' => ['required', 'alpha_num', 'max:10', Rule::unique('brands', 'code')->where('company_id', $r->user()->company_id)->ignore($b?->id)],
            'color' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|string|max:40',
            'address' => 'nullable|string|max:200',
            'registration' => 'nullable|string|max:120',
            'bank_details' => 'nullable|string|max:500',
            'invoice_terms' => 'nullable|string|max:1000',
        ]);
        $d['code'] = strtoupper($d['code']);
        $d['color'] = $d['color'] ?? '#16A34A';
        return $d;
    }
}
