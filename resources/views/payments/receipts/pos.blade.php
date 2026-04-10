<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Receipt {{ $payment->receipt_no }}</title>
    <style>
        body { font-family: "Courier New", monospace; width: 300px; margin: 0 auto; padding: 12px; color: #111; }
        .center { text-align: center; }
        .line { border-top: 1px dashed #555; margin: 8px 0; }
        .row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px; gap: 8px; }
        .bold { font-weight: 700; }
    </style>
</head>
<body>
    <div class="center bold">{{ $payment->tenant?->name ?? 'Coaching Center' }}</div>
    <div class="center">POS Receipt</div>
    <div class="line"></div>
    <div class="row"><span>Receipt</span><span>{{ $payment->receipt_no }}</span></div>
    <div class="row"><span>Student</span><span>{{ $payment->student?->student_code }}</span></div>
    <div class="row"><span>Owner</span><span>{{ $payment->ownerTeacher?->name ?? 'N/A' }}</span></div>
    <div class="row"><span>Collector</span><span>{{ $payment->collector?->name ?? 'N/A' }}</span></div>
    <div class="row"><span>Date</span><span>{{ $payment->collected_on?->format('d/m/Y H:i') }}</span></div>
    <div class="line"></div>
    @foreach ($payment->items as $item)
        <div class="row"><span>{{ $item->feeHead?->code ?? $item->feeHead?->name }}</span><span>{{ number_format((float) $item->paid_amount, 2) }}</span></div>
        <div class="row"><span>Period</span><span>{{ $item->billing_period_key }}</span></div>
    @endforeach
    <div class="line"></div>
    <div class="row bold"><span>Total Paid</span><span>{{ number_format((float) $payment->total_amount, 2) }}</span></div>
</body>
</html>
