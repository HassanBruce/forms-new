<?php

namespace App\Http\Controllers;

use App\Mail\InvoiceMail;
use App\Models\Booking;
use App\Models\Brand;
use App\Models\Payment;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $u = $request->user();
        $base = Booking::query()->visibleTo($u);

        $counts = [
            'all' => (clone $base)->count(),
            'waiting' => (clone $base)->where('status', 'waiting')->count(),
            'accepted' => (clone $base)->where('status', 'accepted')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'unpaid' => (clone $base)->where('total_sale', '>', 0)->whereColumn('total_paid', '<', 'total_sale')->count(),
        ];

        $tab = $request->query('tab', 'all');
        $q = (clone $base)->with(['brand', 'agent', 'passengers']);
        if (in_array($tab, ['waiting', 'accepted', 'rejected', 'draft'], true)) $q->where('status', $tab);
        if ($tab === 'unpaid') $q->where('total_sale', '>', 0)->whereColumn('total_paid', '<', 'total_sale');
        if ($s = trim((string) $request->query('q'))) {
            $q->where(function ($w) use ($s) {
                $like = '%'.$s.'%';
                $w->where('ref', 'like', $like)->orWhere('lead_name', 'like', $like)->orWhere('pnr', 'like', $like)
                  ->orWhere('email', 'like', $like)
                  ->orWhereHas('hotels', fn ($h) => $h->where('hcn', 'like', $like)->orWhere('ref_no', 'like', $like));
            });
        }
        if ($b = $request->query('brand')) $q->where('brand_id', $b);
        if ($a = $request->query('agent')) $q->where('agent_id', $a);
        if ($from = $request->query('from')) $q->whereDate('travel_start', '>=', $from);

        $bookings = $q->latest('id')->paginate(15)->withQueryString();
        $brands = Brand::orderBy('name')->get();
        $agents = \App\Models\User::where('company_id', $u->company_id)->whereIn('role', ['agent', 'team_lead', 'director', 'admin'])->orderBy('name')->get();

        return view('bookings.index', compact('bookings', 'counts', 'tab', 'brands', 'agents'));
    }

    public function invoices(Request $request)
    {
        $bookings = Booking::query()->visibleTo($request->user())->with('brand')
            ->where('total_sale', '>', 0)->latest('id')->paginate(20);
        return view('bookings.invoices', compact('bookings'));
    }

    /** "New booking" creates a draft straight away so every step auto-saves to a real record. */
    public function store(Request $request)
    {
        $u = $request->user();
        abort_unless($u->canCreateBookings(), 403);
        $brandId = $u->brand_id ?: Brand::query()->value('id');
        abort_unless($brandId, 422, 'Create a sub-brand first.');

        $b = new Booking([
            'brand_id' => $brandId, 'team_id' => $u->team_id, 'agent_id' => $u->id,
            'currency' => $u->company->currency, 'status' => 'draft', 'step' => 1,
        ]);
        $b->company_id = $u->company_id;
        $b->ref = $u->company->nextBookingRef();
        $b->save();

        return redirect()->route('bookings.step', [$b, 1]);
    }

    public function show(Request $request, Booking $booking)
    {
        $this->authorizeView($request, $booking);
        return redirect()->route('bookings.step', [$booking, min(max($booking->step, 1), 5)]);
    }

    public function step(Request $request, Booking $booking, int $step)
    {
        $this->authorizeView($request, $booking);
        abort_unless($step >= 1 && $step <= 5, 404);
        $booking->load(['brand', 'agent', 'passengers', 'hotels', 'transports', 'visas', 'prices', 'costs', 'payments.recorder']);

        $data = ['booking' => $booking, 'step' => $step, 'editable' => $request->user()->canEditBooking($booking)];
        $data['suppliers'] = Supplier::orderBy('name')->get()->groupBy('type');

        if ($step === 1) {
            $data['brands'] = $request->user()->hasRole('admin', 'accounts') ? Brand::orderBy('name')->get() : Brand::whereKey($booking->brand_id)->get();
        }
        if ($step === 4 && $booking->costs->isEmpty()) {
            $data['suggestedCosts'] = $this->suggestCosts($booking);
        }
        if ($step === 5) {
            $data['methods'] = Payment::METHODS;
        }

        return view('bookings.step'.$step, $data);
    }

    public function saveStep(Request $request, Booking $booking, int $step)
    {
        $u = $request->user();
        abort_unless($u->canEditBooking($booking), 403);
        abort_unless($step >= 1 && $step <= 5, 404);

        DB::transaction(function () use ($request, $booking, $step, $u) {
            match ($step) {
                1 => $this->saveStep1($request, $booking, $u),
                2 => $this->saveStep2($request, $booking),
                3 => $this->saveStep3($request, $booking),
                4 => $this->saveStep4($request, $booking),
                5 => null,
            };
            $booking->step = max($booking->step, min($step + 1, 5));
            if ($step >= 3 && $booking->status === 'draft' && (float) $booking->total_sale > 0) {
                $booking->status = 'waiting';
            }
            $booking->save();
        });

        if ($step === 5 || $request->input('_action') === 'finish') {
            return redirect()->route('bookings.index')->with('ok', "Booking {$booking->ref} saved.");
        }
        if ($request->input('_action') === 'stay') {
            return back()->with('ok', 'Saved.');
        }
        return redirect()->route('bookings.step', [$booking, $step + 1]);
    }

    private function saveStep1(Request $r, Booking $b, $u): void
    {
        $d = $r->validate([
            'profile_no' => 'nullable|string|max:50',
            'brand_id' => ['required', Rule::exists('brands', 'id')->where('company_id', $u->company_id)],
            'lead_name' => 'required|string|max:120',
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|string|max:40',
            'passengers' => 'required|array|min:1',
            'passengers.*.salutation' => 'nullable|string|max:10',
            'passengers.*.first_name' => 'required|string|max:80',
            'passengers.*.surname' => 'required|string|max:80',
            'passengers.*.type' => 'required|in:adult,child,infant',
        ], ['passengers.required' => 'Add at least one passenger.']);

        // Non-admins cannot move a booking into another brand.
        if (! $u->hasRole('admin') && (int) $d['brand_id'] !== (int) $b->brand_id) $d['brand_id'] = $b->brand_id;

        $b->fill(collect($d)->except('passengers')->all());
        $b->passengers()->delete();
        foreach (array_values($d['passengers']) as $i => $p) {
            $b->passengers()->create($p + ['position' => $i]);
        }
    }

    private function saveStep2(Request $r, Booking $b): void
    {
        $d = $r->validate([
            'pnr' => 'nullable|string|max:20',
            'airline' => 'nullable|string|max:80',
            'flight_supplier' => 'nullable|string|max:120',
            'flight_details' => 'nullable|string|max:5000',
            'hotels' => 'nullable|array',
            'hotels.*.city' => 'nullable|string|max:80',
            'hotels.*.hotel_name' => 'required_with:hotels|string|max:150',
            'hotels.*.room_type' => 'nullable|string|max:50',
            'hotels.*.rooms' => 'nullable|integer|min:1|max:999',
            'hotels.*.meal_type' => 'nullable|string|max:50',
            'hotels.*.view_type' => 'nullable|string|max:50',
            'hotels.*.check_in' => 'nullable|date',
            'hotels.*.check_out' => 'nullable|date|after_or_equal:hotels.*.check_in',
            'hotels.*.supplier' => 'nullable|string|max:120',
            'hotels.*.ref_no' => 'nullable|string|max:80',
            'hotels.*.hcn' => 'nullable|string|max:80',
        ]);
        $b->fill(collect($d)->except('hotels')->all());
        $b->hotels()->delete();
        foreach (array_values($d['hotels'] ?? []) as $h) {
            $h['rooms'] = $h['rooms'] ?? 1;
            $b->hotels()->create($h);
        }
        $b->syncTravelDates();
    }

    private function saveStep3(Request $r, Booking $b): void
    {
        $d = $r->validate([
            'transports' => 'nullable|array',
            'transports.*.vehicle_type' => 'required_with:transports|string|max:80',
            'transports.*.capacity' => 'nullable|integer|min:1|max:999',
            'transports.*.route' => 'nullable|string|max:200',
            'transports.*.supplier' => 'nullable|string|max:120',
            'visas' => 'nullable|array',
            'visas.*.visa_type' => 'required_with:visas|string|max:80',
            'visas.*.supplier' => 'nullable|string|max:120',
            'visas.*.quantity' => 'nullable|integer|min:1|max:999',
            'prices' => 'required|array|min:1',
            'prices.*.pax_type' => 'required|string|max:30',
            'prices.*.quantity' => 'required|integer|min:1|max:999',
            'prices.*.unit_price' => 'required|numeric|min:0|max:9999999',
        ], ['prices.required' => 'Add at least one price row.']);

        $b->transports()->delete();
        foreach (array_values($d['transports'] ?? []) as $t) $b->transports()->create($t);
        $b->visas()->delete();
        foreach (array_values($d['visas'] ?? []) as $v) $b->visas()->create($v + ['quantity' => $v['quantity'] ?? 1]);
        $b->prices()->delete();
        foreach (array_values($d['prices']) as $p) $b->prices()->create($p);
        $b->recalculate();
    }

    private function saveStep4(Request $r, Booking $b): void
    {
        $d = $r->validate([
            'costs' => 'nullable|array',
            'costs.*.category' => 'required|in:flight,hotel,transport,visa,other',
            'costs.*.description' => 'required|string|max:150',
            'costs.*.quantity' => 'required|integer|min:0|max:9999',
            'costs.*.unit_cost' => 'required|numeric|min:0|max:9999999',
        ]);
        $b->costs()->delete();
        foreach (array_values($d['costs'] ?? []) as $c) $b->costs()->create($c);
        $b->recalculate();
    }

    /** Pre-fill the P&L sheet from what was entered in steps 1–3 (costs left at 0 for the agent). */
    private function suggestCosts(Booking $b): array
    {
        $rows = [];
        $pax = $b->passengers->groupBy('type')->map->count();
        if ($b->pnr || $b->flight_details) {
            foreach (['adult' => 'Adult flight', 'child' => 'Child flight', 'infant' => 'Infant flight'] as $k => $l) {
                if ($n = $pax->get($k)) $rows[] = ['category' => 'flight', 'description' => $l, 'quantity' => $n, 'unit_cost' => ''];
            }
        }
        foreach ($b->hotels as $h) {
            $rows[] = ['category' => 'hotel', 'description' => trim(($h->city ? $h->city.' · ' : '').$h->hotel_name), 'quantity' => max(1, $h->nights()), 'unit_cost' => ''];
        }
        foreach ($b->transports as $t) {
            $rows[] = ['category' => 'transport', 'description' => trim($t->vehicle_type.($t->route ? ' · '.$t->route : '')), 'quantity' => 1, 'unit_cost' => ''];
        }
        foreach ($b->visas as $v) {
            $rows[] = ['category' => 'visa', 'description' => $v->visa_type, 'quantity' => $v->quantity, 'unit_cost' => ''];
        }
        return $rows;
    }

    public function updateStatus(Request $request, Booking $booking)
    {
        abort_unless($request->user()->canEditBooking($booking), 403);
        $d = $request->validate(['status' => 'required|in:waiting,accepted,rejected']);
        $booking->update($d);
        return back()->with('ok', 'Customer status set to '.Booking::STATUSES[$d['status']].'.');
    }

    public function invoice(Request $request, Booking $booking)
    {
        $this->authorizeView($request, $booking);
        $this->ensureInvoiceNo($booking);
        $booking->load(['brand', 'passengers', 'hotels', 'transports', 'visas', 'prices', 'payments']);
        return view('bookings.invoice', ['booking' => $booking, 'company' => $booking->company, 'print' => true]);
    }

    public function sendInvoice(Request $request, Booking $booking)
    {
        abort_unless($request->user()->canEditBooking($booking), 403);
        if (! $booking->email) {
            return back()->withErrors(['email' => 'Add the lead guest’s email in step 1 before sending the invoice.']);
        }
        $this->ensureInvoiceNo($booking);
        $booking->load(['brand', 'passengers', 'hotels', 'transports', 'visas', 'prices', 'payments', 'company']);
        Mail::to($booking->email, $booking->lead_name)->send(new InvoiceMail($booking));
        $booking->forceFill(['invoice_sent_at' => now()])->save();
        if ($booking->status === 'draft') $booking->update(['status' => 'waiting']);
        return back()->with('ok', "Invoice {$booking->invoice_no} emailed to {$booking->email}.");
    }

    public function destroy(Request $request, Booking $booking)
    {
        $u = $request->user();
        abort_unless($u->canEditBooking($booking) && ($booking->status === 'draft' || $u->hasRole('admin', 'director')), 403);
        $booking->delete();
        return redirect()->route('bookings.index')->with('ok', "Booking {$booking->ref} deleted.");
    }

    private function ensureInvoiceNo(Booking $b): void
    {
        if (! $b->invoice_no) {
            $b->forceFill(['invoice_no' => $b->company->nextInvoiceNo()])->save();
        }
    }

    private function authorizeView(Request $r, Booking $b): void
    {
        abort_unless($r->user()->canViewBooking($b), 403);
    }
}
