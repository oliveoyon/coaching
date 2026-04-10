<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Normal Print {{ $payment->receipt_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #111827; margin: 18px; }
        .header { margin-bottom: 14px; }
        .header h1 { margin: 0 0 4px; font-size: 18px; }
        .meta { font-size: 13px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #d1d5db; padding: 8px; font-size: 13px; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $payment->tenant?->name ?? 'Coaching Center' }} Receipt</h1>
        <div class="meta">
            Receipt: {{ $payment->receipt_no }} |
            Student: {{ $payment->student?->name }} ({{ $payment->student?->student_code }}) |
            Owner: {{ $payment->ownerTeacher?->name ?? 'N/A' }} |
            Collector: {{ $payment->collector?->name ?? 'N/A' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Head</th>
                <th>Period</th>
                <th>Charge</th>
                <th>Paid</th>
                <th>Due After</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payment->items as $item)
                <tr>
                    <td>{{ $item->feeHead?->name }}</td>
                    <td>{{ $item->billing_period_key }}</td>
                    <td>{{ number_format((float) $item->charge_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->paid_amount, 2) }}</td>
                    <td>{{ number_format((float) $item->due_after, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
