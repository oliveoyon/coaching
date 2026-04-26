<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
foreach (App\Models\Enrollment::query()->with(['student','batch'])->orderBy('id')->get() as $enrollment) {
    echo 'ENR '.$enrollment->id.' | '.$enrollment->student->student_code.' | '.$enrollment->batch->name.' | '.$enrollment->status.PHP_EOL;
}
echo '---'.PHP_EOL;
foreach (App\Models\Payment::query()->with(['enrollment.student','enrollment.batch'])->orderBy('id')->get() as $payment) {
    echo 'PAY '.$payment->id.' | ENR '.$payment->enrollment_id.' | '.$payment->enrollment->student->student_code.' | '.$payment->enrollment->batch->name.' | '.$payment->month.' | '.$payment->amount.' | '.$payment->method.' | '.$payment->status.' | '.($payment->transaction_id ?? '-').PHP_EOL;
}
