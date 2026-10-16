<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /** Finance overview: every visible booking with an outstanding or recent balance. */
    public function index(Request $request)
    {
        $u = $request->user();
        $base = Booking::query()->visibleTo($u)->where('total_sale', '>', 0);
        $totals = [
            'invoiced' => (float) (clone $base)->sum('total_sale'),
            'paid' => (float) (clone $base)->sum('total_paid'),
        ];
        $totals['pending'] = max(0, $totals['invoiced'] - $totals['paid']);
        $filter = $request->query('show', 'pending');
        $q = (clone $base)->with(['brand', 'agent']);
        if ($filter === 'pending') $q->whereColumn('total_paid', '<', 'total_sale');
        $bookings = $q->latest('id')->paginate(20)->withQueryString();
        $recent = Payment::whereHas('booking', fn ($b) => $b->visibleTo($u))->with('booking')->latest('paid_on')->latest('id')->limit(8)->get();
        return view('payments.index', compact('bookings', 'totals', 'filter', 'recent'));
    }

    public function store(Request $request, Booking $booking)
    {
        $u = $request->user();
        abort_unless($u->canViewBooking($booking) && $u->canRecordPayments(), 403);
        $d = $request->validate([
            'paid_on' => 'required|date|before_or_equal:today',
            'reference' => 'nullable|string|max:80',
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'amount' => 'required|numeric|min:0.01|max:9999999',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);
        $p = new Payment(collect($d)->except('proof')->all());
        $p->booking_id = $booking->id;
        $p->recorded_by = $u->id;
        if ($request->hasFile('proof')) {
            $p->proof_path = $request->file('proof')->store("proofs/{$booking->company_id}", 'local');
        }
        $p->save();
        $booking->recalculate();
        return back()->with('ok', 'Payment of '.$booking->company->money($p->amount).' recorded.');
    }

    public function destroy(Request $request, Payment $payment)
    {
        $b = $payment->booking;
        $u = $request->user();
        abort_unless($b && $u->canViewBooking($b) && $u->canRecordPayments(), 403);
        if ($payment->proof_path) Storage::disk('local')->delete($payment->proof_path);
        $payment->delete();
        $b->recalculate();
        return back()->with('ok', 'Payment removed.');
    }

    /** Proofs live on the private disk; served only to users who can see the booking. */
    public function proof(Request $request, Payment $payment)
    {
        abort_unless($payment->booking && $request->user()->canViewBooking($payment->booking) && $payment->proof_path, 404);
        return Storage::disk('local')->response($payment->proof_path);
    }
}
