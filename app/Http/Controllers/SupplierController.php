<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    public function index()
    {
        return view('suppliers.index', ['suppliers' => Supplier::orderBy('type')->orderBy('name')->get()->groupBy('type')]);
    }

    public function store(Request $request)
    {
        abort_if($request->user()->hasRole('agent'), 403);
        Supplier::create($request->validate([
            'name' => 'required|string|max:120',
            'type' => ['required', Rule::in(array_keys(Supplier::TYPES))],
            'contact' => 'nullable|string|max:190',
        ]));
        return back()->with('ok', 'Supplier added.');
    }

    public function destroy(Request $request, Supplier $supplier)
    {
        abort_if($request->user()->hasRole('agent'), 403);
        $supplier->delete();
        return back()->with('ok', 'Supplier removed.');
    }
}
