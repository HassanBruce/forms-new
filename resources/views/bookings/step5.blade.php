@php $c = $booking->company; $canPay = auth()->user()->canRecordPayments(); @endphp
<x-wizard :booking="$booking" :step="5" :editable="$editable" :formless="true">
    <section class="card space-y-4 p-6">
        <div class="grid divide-y divide-line sm:grid-cols-3 sm:divide-x sm:divide-y-0">
            <div class="pb-4 sm:pr-6 sm:pb-0"><p class="eyebrow">Total invoice</p><p class="mt-1.5 text-[32px] leading-10 font-bold">{{ $c->money($booking->total_sale) }}</p></div>
            <div class="py-4 sm:px-6 sm:py-0"><p class="eyebrow">Total paid</p><p class="mt-1.5 text-[32px] leading-10 font-bold text-green-600">{{ $c->money($booking->total_paid) }}</p></div>
            <div class="pt-4 sm:pl-6 sm:pt-0"><p class="eyebrow">Total pending</p><p class="mt-1.5 text-[32px] leading-10 font-bold text-amber-600">{{ $c->money($booking->pending()) }}</p></div>
        </div>
        <div class="h-2 rounded bg-line-soft"><div class="h-2 rounded bg-green-600" style="width: {{ $booking->paidPct() }}%"></div></div>
        <div class="flex items-center text-[13px] text-ink-500"><span class="flex-1">{{ $booking->paidPct() }}% collected · {{ $booking->payments->count() }} {{ Str::plural('payment', $booking->payments->count()) }}</span>
            <x-badge :status="$booking->paymentStatus()" :label="['paid' => 'Paid', 'partial' => 'Partial', 'unpaid' => 'Unpaid', 'none' => 'No invoice'][$booking->paymentStatus()]"/></div>
    </section>

    <section class="card space-y-3 p-6">
        <x-card-head icon="wallet" title="Payments received" sub="Every installment with its proof of payment" tone="success"/>
        <div class="overflow-x-auto">
        <table class="w-full min-w-[640px]">
            <thead class="bg-line-soft"><tr><th class="th rounded-l-lg">Sr #</th><th class="th">Date</th><th class="th">Reference</th><th class="th">Payment mode</th><th class="th">Proof</th><th class="th text-right">Amount</th><th class="th rounded-r-lg"></th></tr></thead>
            <tbody>
            @forelse($booking->payments as $i => $p)
                <tr class="border-b border-line-soft">
                    <td class="td font-semibold text-ink-400">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</td>
                    <td class="td whitespace-nowrap">{{ $p->paid_on->format('d M Y') }}</td>
                    <td class="td font-semibold text-ink-900">{{ $p->reference ?: '—' }}</td>
                    <td class="td">{{ $p->methodLabel() }}</td>
                    <td class="td">@if($p->proof_path)<a href="{{ route('payments.proof', $p) }}" target="_blank" class="inline-flex items-center gap-2 rounded-lg border border-line py-1 pr-2.5 pl-1 text-xs hover:bg-canvas"><span class="grid size-[26px] place-items-center rounded-md bg-sky-50 text-sky-600"><x-icon name="image" class="size-3.5"/></span>{{ Str::limit(basename($p->proof_path), 14) }}</a>@else<span class="text-ink-400">—</span>@endif</td>
                    <td class="td text-right font-semibold text-ink-900">{{ $c->money($p->amount) }}</td>
                    <td class="td text-right">@if($canPay)<form method="POST" action="{{ route('payments.destroy', $p) }}" onsubmit="return confirm('Remove this payment?')">@csrf @method('DELETE')<button class="text-ink-400 hover:text-red-600"><x-icon name="trash" class="size-[18px]"/></button></form>@endif</td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-8 text-center text-sm text-ink-500">No payments recorded yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </section>

    @if($canPay && $booking->total_sale > 0)
    <form method="POST" action="{{ route('payments.store', $booking) }}" enctype="multipart/form-data" class="card space-y-4 p-6" x-data="{ file: '' }">
        @csrf
        <x-card-head icon="plus" title="Record a payment" sub="Accounts team · updates paid & pending automatically"/>
        <div class="grid gap-4 md:grid-cols-4">
            <div><label class="label">Date</label><input type="date" name="paid_on" value="{{ old('paid_on', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required class="input"></div>
            <div><label class="label">Reference</label><input name="reference" value="{{ old('reference') }}" class="input" placeholder="e.g. HSBC-10293"></div>
            <div><label class="label">Payment mode</label><select name="method" class="input">@foreach($methods as $k => $l)<option value="{{ $k }}" @selected(old('method') === $k)>{{ $l }}</option>@endforeach</select></div>
            <div><label class="label">Amount ({{ $c->currency }})</label><input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount', $booking->pending() > 0 ? number_format($booking->pending(), 2, '.', '') : '') }}" required class="input"></div>
        </div>
        <label class="flex cursor-pointer flex-wrap items-center gap-3 rounded-xl border border-dashed border-line bg-canvas p-4">
            <span class="grid size-10 place-items-center rounded-[10px] border border-line bg-white text-brand-600"><x-icon name="upload"/></span>
            <span class="min-w-0 flex-1"><span class="block text-sm font-semibold" x-text="file || 'Upload payment screenshot'"></span><span class="block text-[13px] text-ink-500">PNG, JPG or PDF up to 5 MB — click to browse</span></span>
            <input type="file" name="proof" accept="image/*,.pdf" class="hidden" @change="file = $event.target.files[0]?.name || ''">
            <button class="btn btn-primary"><x-icon name="plus" class="size-[18px]"/>Add payment</button>
        </label>
    </form>
    @elseif(! $canPay)
        <p class="rounded-[10px] bg-line-soft px-4 py-3 text-[13px] text-ink-500">Payments are recorded by Accounts, Directors and Admins.</p>
    @endif
</x-wizard>
