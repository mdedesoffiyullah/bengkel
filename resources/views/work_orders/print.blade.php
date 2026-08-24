<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota {{ $workOrder->code }}</title>
    <style>
        @page { size: 88mm auto; margin: 0; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; width: 88mm; background: #fff; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 11px; line-height: 1.35; }
        body { padding: 4mm; }
        .receipt { width: 80mm; margin: 0 auto; }
        .center { text-align: center; }
        .title { font-size: 18px; font-weight: 700; }
        .subtitle { font-size: 10px; }
        .bold { font-weight: 700; }
        .section { margin-top: 8px; }
        .line { border-top: 1px dashed #000; margin: 7px 0; }
        .row { display: flex; justify-content: space-between; gap: 8px; }
        .item { margin: 0 0 6px; }
        .item-name { font-weight: 700; }
        .item-detail { display: flex; justify-content: space-between; gap: 8px; }
        .item-detail .left { white-space: nowrap; }
        .item-detail .right { text-align: right; white-space: nowrap; }
        .grand-total { font-size: 17px; font-weight: 700; }
        .footer { margin-top: 14px; text-align: center; font-size: 10px; }
        .print-button { width: 100%; margin-bottom: 10px; padding: 8px; border: 1px solid #999; background: #eee; cursor: pointer; }
        @media print { .print-button { display: none; } body { padding: 3mm; } }
    </style>
</head>
<body>
<div class="receipt">
    <button type="button" class="print-button" onclick="window.print()">🖨 CETAK</button>

    <div class="center">
        <div class="title">BENGKEL</div>
        <div class="subtitle">MANAGEMENT SYSTEM</div>
    </div>

    <div class="line"></div>

    <div class="row"><span>WO</span><span class="bold">{{ $workOrder->code }}</span></div>
    <div class="row"><span>Tanggal</span><span>{{ optional($workOrder->opened_at)->format('d/m/Y H:i') }}</span></div>

    <div class="line"></div>

    <div class="bold">CUSTOMER</div>
    <div>{{ $workOrder->customer->name ?? '-' }}</div>
    @if($workOrder->customer?->phone)<div>{{ $workOrder->customer->phone }}</div>@endif

    <div class="section bold">KENDARAAN</div>
    <div>{{ trim(($workOrder->customer->brand ?? '') . ' ' . ($workOrder->customer->type ?? '')) ?: '-' }}</div>
    <div>Nopol: {{ $workOrder->customer->plate_number ?? '-' }}</div>

    <div class="line"></div>
    <div class="bold">PEKERJAAN / SPAREPART</div>

    <div class="section">
        @forelse($workOrder->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->item_name }}</div>
                <div class="item-detail">
                    <div class="left">{{ rtrim(rtrim(number_format((float) $item->quantity, 3, ',', '.'), '0'), ',') }} x Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</div>
                    <div class="right">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</div>
                </div>
                @if((float) $item->discount_amount > 0)
                    <div class="item-detail"><div>Discount</div><div>- Rp {{ number_format((float) $item->discount_amount, 0, ',', '.') }}</div></div>
                @endif
            </div>
        @empty
            <div>Belum ada item.</div>
        @endforelse
    </div>

    <div class="line"></div>
    <div class="row"><span>Subtotal</span><span>Rp {{ number_format((float) $workOrder->subtotal, 0, ',', '.') }}</span></div>
    @if((float) $workOrder->discount > 0)
        <div class="row"><span>Discount</span><span>- Rp {{ number_format((float) $workOrder->discount, 0, ',', '.') }}</span></div>
    @endif
    <div class="line"></div>
    <div class="row grand-total"><span>TOTAL</span><span>Rp {{ number_format((float) $workOrder->grand_total, 0, ',', '.') }}</span></div>

    @php
        $totalPaid = (float) ($workOrder->payments?->sum('amount') ?? 0);
        $remaining = max(0, (float) $workOrder->grand_total - $totalPaid);
    @endphp

    <div class="line"></div>
    <div class="bold">PEMBAYARAN</div>
    <div class="row"><span>Sudah Dibayar</span><span>Rp {{ number_format($totalPaid, 0, ',', '.') }}</span></div>
    <div class="row"><span>Sisa</span><span>Rp {{ number_format($remaining, 0, ',', '.') }}</span></div>

    <div class="line"></div>
    <div class="footer">
        <div class="bold">TERIMA KASIH</div>
        <div>ATAS KEPERCAYAAN ANDA</div>
    </div>
</div>
</body>
</html>
