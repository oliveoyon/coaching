<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt {{ $payment->receipt_no }}</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; color: #1f2937; margin: 0; padding: 24px; }
        .sheet { max-width: 820px; margin: 0 auto; background: #fff; border: 1px solid #dbe1ea; border-radius: 14px; padding: 28px; }
        .top { display: flex; justify-content: space-between; align-items: start; margin-bottom: 24px; }
        .title { font-size: 24px; font-weight: 700; margin: 0 0 6px; }
        .muted { color: #6b7280; font-size: 13px; }
        .grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .label { font-size: 12px; color: #6b7280; margin-bottom: 4px; }
        .value { font-size: 15px; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border-bottom: 1px solid #e5e7eb; padding: 10px 8px; text-align: left; font-size: 14px; }
        th { background: #f8fafc; }
        .summary { margin-top: 20px; text-align: right; font-size: 20px; font-weight: 700; }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="top">
            <div>
                <div class="title">{{ $payment->tenant?->name ?? 'Coaching Center' }}</div>
                <div class="muted">Printable Receipt</div>
            </div>
            <div style="text-align:right;">
                <div class="value">{{ $payment->receipt_no }}</div>
                <div class="muted">{{ $payment->collected_on?->format('d M Y h:i A') }}</div>
            </div>
        </div>

        <div class="grid">
            <div>
                <div class="label">Student</div>
                <div class="value">{{ $payment->student?->name }}</div>
                <div class="muted">{{ $payment->student?->student_code }}</div>
            </div>
            <div>
                <div class="label">Owner Teacher</div>
                <div class="value">{{ $payment->ownerTeacher?->name ?? 'Not set' }}</div>
                <div class="muted">Academic owner</div>
            </div>
            <div>
                <div class="label">Collector</div>
                <div class="value">{{ $payment->collector?->name }}</div>
                <div class="muted">{{ str($payment->collector_role)->replace('_', ' ')->title() }}</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Fee Head</th>
                    <th>Structure</th>
                    <th>Period</th>
                    <th>Paid</th>
                    <th>Due After</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payment->items as $item)
                    <tr>
                        <td>{{ $item->feeHead?->name }}</td>
                        <td>{{ $item->feeStructure?->title }}</td>
                        <td>{{ $item->billing_period_key }}</td>
                        <td>{{ number_format((float) $item->paid_amount, 2) }}</td>
                        <td>{{ number_format((float) $item->due_after, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="summary">Paid Total: {{ number_format((float) $payment->total_amount, 2) }}</div>
    </div>
</body>
</html>
