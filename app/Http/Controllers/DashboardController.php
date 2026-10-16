<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $u = $request->user();
        $month = $request->query('month') ? \Carbon\Carbon::parse($request->query('month').'-01') : now()->startOfMonth();
        $base = fn () => Booking::query()->visibleTo($u)->where('status', '!=', 'rejected');
        $inMonth = fn () => $base()->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);

        $sales = (float) $inMonth()->sum('total_sale');
        $purchase = (float) $inMonth()->sum('total_purchase');
        $prev = $month->copy()->subMonth();
        $prevSales = (float) $base()->whereBetween('created_at', [$prev->copy()->startOfMonth(), $prev->copy()->endOfMonth()])->sum('total_sale');

        $all = Booking::query()->visibleTo($u)->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
        $statusCounts = (clone $all)->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        $outstandingQ = Booking::query()->visibleTo($u)->where('status', '!=', 'rejected')->where('total_sale', '>', 0)->whereColumn('total_paid', '<', 'total_sale');
        $kpi = [
            'sales' => $sales,
            'growth' => $prevSales > 0 ? round(($sales - $prevSales) / $prevSales * 100, 1) : null,
            'margin' => $sales - $purchase,
            'margin_pct' => $sales > 0 ? round(($sales - $purchase) / $sales * 100, 1) : 0,
            'outstanding' => (float) (clone $outstandingQ)->selectRaw('sum(total_sale - total_paid) s')->value('s'),
            'outstanding_count' => (clone $outstandingQ)->count(),
            'bookings' => (int) $statusCounts->sum(),
            'accepted' => (int) ($statusCounts['accepted'] ?? 0),
        ];

        // Last 12 months, sales vs purchase.
        $chart = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = $month->copy()->subMonths($i);
            $q = $base()->whereBetween('created_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()]);
            $chart[] = ['label' => $m->format('M'), 'sale' => (float) (clone $q)->sum('total_sale'), 'purchase' => (float) (clone $q)->sum('total_purchase')];
        }
        $chartMax = max(1, collect($chart)->max(fn ($c) => max($c['sale'], $c['purchase'])));

        $top = Booking::query()->visibleTo($u)->where('status', '!=', 'rejected')
            ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
            ->selectRaw('agent_id, sum(total_sale) s, count(*) c')->groupBy('agent_id')->orderByDesc('s')->first();
        $topAgent = $top ? ['user' => User::find($top->agent_id), 'sales' => (float) $top->s, 'count' => (int) $top->c] : null;

        $recent = Booking::query()->visibleTo($u)->with(['agent', 'passengers'])->latest('id')->limit(6)->get();

        return view('dashboard', compact('kpi', 'chart', 'chartMax', 'statusCounts', 'recent', 'month', 'topAgent'));
    }
}
