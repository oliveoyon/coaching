<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::role('Super Admin')->orderBy('id')->first();

if (! $user) {
    fwrite(STDERR, "No Super Admin user found.\n");
    exit(1);
}

$expiresAt = '2026-06-30 23:59:59';
$created = 0;
$updated = 0;

$batches = App\Models\Batch::query()
    ->where('status', 'active')
    ->orderBy('name')
    ->get();

foreach ($batches as $batch) {
    $title = $batch->name . ' Admission Form';

    $attributes = ['batch_id' => $batch->id];
    $values = [
        'title' => $title,
        'status' => 'active',
        'expires_at' => $expiresAt,
        'created_by' => $user->id,
    ];

    $existing = App\Models\BatchAdmissionLink::query()
        ->where('batch_id', $batch->id)
        ->first();

    if ($existing) {
        $existing->fill($values);

        if (! $existing->token) {
            $existing->token = Illuminate\Support\Str::slug($batch->name) . '-' . Illuminate\Support\Str::random(6);
        }

        $existing->save();
        $updated++;
        continue;
    }

    App\Models\BatchAdmissionLink::create($values + [
        'batch_id' => $batch->id,
        'token' => Illuminate\Support\Str::slug($batch->name) . '-' . Illuminate\Support\Str::random(6),
    ]);

    $created++;
}

echo 'batches=' . $batches->count() . PHP_EOL;
echo 'created=' . $created . PHP_EOL;
echo 'updated=' . $updated . PHP_EOL;
echo 'expires_at=' . $expiresAt . PHP_EOL;
