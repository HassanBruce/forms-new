@php $c = auth()->user()->company; @endphp
<x-layouts.app title="Invoices">
    <x-page-header title="Invoices" sub="Every priced booking and where its invoice stands"/>
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[820px]">
            <thead class="bg-line-soft"><tr><th class="th">Invoice</th><th class="th">Booking</th><th class="th">Customer</th><th class="th">Sent</th><th class="th text-right">Total</th><th class="th text-right">Balance</th><th class="th">Payment</th><th class="th"></th></tr></thead>
            <tbody>
            @forelse($bookings as $b)
                <tr class="border-t border-line-soft">
                    <td class="td font-semibold text-ink-900">{{ $b->invoice_no ?? '—' }}</td>
                    <td class="td"><a class="font-semibold text-brand-600" href="{{ route('bookings.show', $b) }}">{{ $b->ref }}</a></td>
                    <td class="td">{{ $b->lead_name }}<p class="text-xs text-ink-500">{{ $b->email }}</p></td>
                    <td class="td">{{ $b->invoice_sent_at?->format('d M Y') ?? 'Not sent' }}</td>
                    <td class="td text-right font-semibold text-ink-900">{{ $c->money($b->total_sale) }}</td>
                    <td class="td text-right">{{ $c->money($b->pending()) }}</td>
                    <td class="td"><x-badge :status="$b->paymentStatus()"/></td>
                    <td class="td text-right"><a href="{{ route('bookings.invoice', $b) }}" target="_blank" class="btn btn-ghost btn-sm"><x-icon name="eye" class="size-4"/>View</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-5 py-14 text-center text-sm text-ink-500">No priced bookings yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $bookings->links() }}
</x-layouts.app>
