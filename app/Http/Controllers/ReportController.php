<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function profitLoss(Request $request)
    {
        $u = $request->user();
        $from = $request->date('from') ?? now()->startOfMonth();
        $to = $request->date('to') ?? now()->endOfMonth();
        $group = in_array($request->query('by'), ['brand', 'agent'], true) ? $request->query('by') : 'agent';

        $q = Booking::query()->visibleTo($u)->where('status', '!=', 'rejected')->where('total_sale', '>', 0)
            ->whereBetween('created_at', [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);

        $rows = (clone $q)->with($group)->get()->groupBy($group.'_id')->map(function ($items) use ($group) {
            $sale = $items->sum(fn ($b) => (float) $b->total_sale);
            $buy = $items->sum(fn ($b) => (float) $b->total_purchase);
            return [
                'name' => optional($items->first()->{$group})->name ?? '—',
                'count' => $items->count(), 'sale' => $sale, 'purchase' => $buy, 'margin' => $sale - $buy,
                'pct' => $sale > 0 ? round(($sale - $buy) / $sale * 100, 1) : 0,
            ];
        })->sortByDesc('margin')->values();

        $bookings = (clone $q)->with(['agent', 'brand'])->latest('id')->limit(50)->get();
        $tot = ['sale' => $rows->sum('sale'), 'purchase' => $rows->sum('purchase')];
        $tot['margin'] = $tot['sale'] - $tot['purchase'];

        return view('reports.profit-loss', compact('rows', 'bookings', 'tot', 'from', 'to', 'group'));
    }
}
