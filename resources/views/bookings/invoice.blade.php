@php $b = $booking; $br = $b->brand; $c = $company; @endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Invoice {{ $b->invoice_no }} · {{ $br->name }}</title>
<style>
  body{margin:0;background:{{ $print ? '#F6F7FB' : '#ffffff' }};font-family:'Plus Jakarta Sans',-apple-system,'Segoe UI',Roboto,Arial,sans-serif;color:#0F172A;font-size:14px;line-height:20px}
  .page{max-width:794px;margin:{{ $print ? '32px auto' : '0 auto' }};background:#fff;padding:48px 52px;box-sizing:border-box;{{ $print ? 'box-shadow:0 8px 24px -4px rgba(15,23,42,.08);border-radius:6px;' : '' }}}
  table{width:100%;border-collapse:collapse} td,th{vertical-align:top}
  .muted{color:#64748B} .small{font-size:13px} .xs{font-size:12px}
  .ov{font-size:11px;font-weight:600;letter-spacing:.6px;text-transform:uppercase;color:#64748B}
  .box{background:#F6F7FB;border-radius:12px;padding:16px 20px}
  .it td{padding:8px 0;border-bottom:1px solid #F1F5F9}
  .pt th{background:#F1F5F9;padding:10px 16px;text-align:left} .pt td{padding:12px 16px;border-top:1px solid #F1F5F9}
  .r{text-align:right} .b{font-weight:600}
  .due{background:#0B1020;color:#fff;border-radius:10px;padding:14px 16px}
  .bank{border:1px dashed #E2E8F0;border-radius:12px;padding:16px 20px}
  .badge{display:inline-block;border-radius:999px;padding:3px 10px;font-size:12px;font-weight:500}
  .toolbar{max-width:794px;margin:24px auto 0;display:flex;gap:8px;justify-content:flex-end}
  .toolbar a,.toolbar button{font:600 14px inherit;font-family:inherit;border:1px solid #E2E8F0;background:#fff;border-radius:10px;padding:9px 14px;cursor:pointer;color:#0F172A;text-decoration:none}
  .toolbar .p{background:#4F46E5;color:#fff;border-color:#4F46E5}
  @media print{body{background:#fff}.toolbar{display:none}.page{margin:0;box-shadow:none;padding:24px}}
</style>
</head>
<body>
@if($print)
<div class="toolbar">
  <a href="{{ route('bookings.step', [$b, 3]) }}">← Back to booking</a>
  <button class="p" onclick="window.print()">Print / Save as PDF</button>
</div>
@endif
<div class="page">
  <table><tr>
    <td>
      <table style="width:auto"><tr>
        <td style="padding-right:10px"><div style="width:40px;height:40px;border-radius:10px;background:{{ $br->color }};color:#fff;font-weight:700;text-align:center;line-height:40px">{{ $br->initials() }}</div></td>
        <td style="vertical-align:middle;font-size:18px;font-weight:600">{{ $br->name }}</td>
      </tr></table>
      <div class="small muted" style="margin-top:8px">
        @if($br->address){{ $br->address }}<br>@endif
        {{ collect([$br->phone, $br->email])->filter()->join(' · ') }}@if($br->phone || $br->email)<br>@endif
        @if($br->registration){{ $br->registration }}@endif
      </div>
    </td>
    <td class="r">
      <div style="font-size:32px;line-height:40px;font-weight:700;color:#4F46E5">INVOICE</div>
      <div class="b">{{ $b->invoice_no }}</div>
      <div class="small muted">Issued {{ ($b->invoice_sent_at ?? now())->format('d M Y') }} · Booking {{ $b->ref }}</div>
      @php $ps = $b->paymentStatus(); $col = ['paid' => ['#DCFCE7','#16A34A','Paid'], 'partial' => ['#E0F2FE','#0284C7','Partially paid'], 'unpaid' => ['#FEF3C7','#D97706','Awaiting payment'], 'none' => ['#F1F5F9','#64748B','Draft']][$ps]; @endphp
      <div style="margin-top:8px"><span class="badge" style="background:{{ $col[0] }};color:{{ $col[1] }}">{{ $col[2] }}</span></div>
    </td>
  </tr></table>

  <div class="box" style="margin-top:28px"><table><tr>
    <td style="width:34%"><div class="ov">Bill to</div><div class="b">{{ $b->lead_name }}</div><div class="xs muted">{{ $b->email }}@if($b->phone)<br>{{ $b->phone }}@endif</div></td>
    <td style="width:36%"><div class="ov">Booking</div><div class="b">{{ $b->ref }}{{ $b->pnr ? ' · PNR '.$b->pnr : '' }}</div><div class="xs muted">{{ $b->travelRange() }}</div></td>
    <td><div class="ov">Passengers</div><div class="b">{{ $b->passengers->count() }} {{ Str::plural('traveller', $b->passengers->count()) }}</div><div class="xs muted">{{ $b->paxSummary() }}</div></td>
  </tr></table></div>

  @if($b->passengers->isNotEmpty())
  <div class="ov" style="color:#4F46E5;margin-top:28px">Travellers</div>
  <table class="it">@foreach($b->passengers as $p)<tr><td>{{ $p->salutation }} {{ $p->first_name }} {{ $p->surname }}</td><td class="r small muted">{{ ucfirst($p->type) }}</td></tr>@endforeach</table>
  @endif

  @if($b->hotels->isNotEmpty() || $b->transports->isNotEmpty() || $b->visas->isNotEmpty() || $b->pnr)
  <div class="ov" style="color:#4F46E5;margin-top:28px">Itinerary</div>
  <table class="it">
    @if($b->pnr || $b->airline)<tr><td>Flights{{ $b->airline ? ' · '.$b->airline : '' }}</td><td class="r small muted">PNR {{ $b->pnr ?: '—' }}</td></tr>@endif
    @foreach($b->hotels as $h)
      <tr><td>{{ $h->hotel_name }}{{ $h->city ? ', '.$h->city : '' }} · {{ collect([$h->room_type ? $h->room_type.' × '.$h->rooms : null, $h->meal_type, $h->view_type])->filter()->join(' · ') }}</td>
          <td class="r small muted" style="white-space:nowrap">@if($h->check_in){{ $h->check_in->format('d M') }} – {{ $h->check_out?->format('d M') }} · {{ $h->nights() }} nights @endif</td></tr>
    @endforeach
    @foreach($b->transports as $t)<tr><td>{{ $t->vehicle_type }}{{ $t->route ? ' · '.$t->route : '' }}</td><td class="r small muted">Transfer</td></tr>@endforeach
    @foreach($b->visas as $v)<tr><td>{{ $v->visa_type }}</td><td class="r small muted">{{ $v->quantity }} {{ Str::plural('visa', $v->quantity) }}</td></tr>@endforeach
  </table>
  @endif

  <table class="pt" style="margin-top:28px;border:1px solid #E2E8F0;border-radius:12px;border-collapse:separate;border-spacing:0;overflow:hidden">
    <tr><th class="ov">Package</th><th class="ov r">Qty</th><th class="ov r">Unit price</th><th class="ov r">Amount</th></tr>
    @foreach($b->prices as $p)
      <tr><td>Package — {{ $p->pax_type }}</td><td class="r">{{ $p->quantity }}</td><td class="r">{{ $c->money($p->unit_price) }}</td><td class="r b">{{ $c->money($p->total()) }}</td></tr>
    @endforeach
  </table>

  <table style="margin-top:20px"><tr><td></td><td style="width:300px">
    <table>
      <tr><td class="muted" style="padding:5px 0">Total package</td><td class="r b">{{ $c->money($b->total_sale) }}</td></tr>
      <tr><td class="muted" style="padding:5px 0">Paid to date</td><td class="r b" style="color:#16A34A">− {{ $c->money($b->total_paid) }}</td></tr>
    </table>
    <div class="due" style="margin-top:10px"><table><tr><td style="color:#A3ADC2;font-weight:600;vertical-align:middle">Balance due</td><td class="r" style="font-size:18px;font-weight:600;color:#fff">{{ $c->money($b->pending()) }} {{ $c->currency }}</td></tr></table></div>
  </td></tr></table>

  @if($b->payments->isNotEmpty())
  <div class="ov" style="color:#4F46E5;margin-top:28px">Payments received</div>
  <table class="it">@foreach($b->payments as $p)<tr><td>{{ $p->paid_on->format('d M Y') }} · {{ $p->methodLabel() }}{{ $p->reference ? ' · '.$p->reference : '' }}</td><td class="r b">{{ $c->money($p->amount) }}</td></tr>@endforeach</table>
  @endif

  @if($br->bank_details || $br->invoice_terms)
  <div class="bank" style="margin-top:28px"><table><tr>
    @if($br->bank_details)<td style="padding-right:24px"><div class="ov">Pay by bank transfer</div><div class="small" style="white-space:pre-line;margin-top:4px">{{ $br->bank_details }}
Reference: {{ $b->ref }}</div></td>@endif
    @if($br->invoice_terms)<td style="width:280px"><div class="ov">Terms</div><div class="xs muted" style="white-space:pre-line;margin-top:4px">{{ $br->invoice_terms }}</div></td>@endif
  </tr></table></div>
  @endif

  <table style="margin-top:28px"><tr><td class="small muted">Thank you for travelling with {{ $br->name }}</td><td class="r xs" style="color:#94A3B8">Powered by Tripdesk</td></tr></table>
</div>
</body>
</html>
