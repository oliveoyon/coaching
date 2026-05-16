<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$tables = [
    'teacher_settlement_items',
    'teacher_settlements',
    'distributions',
    'payments',
    'attendance_records',
    'attendance_sessions',
    'student_face_registrations',
    'admission_requests',
    'batch_admission_links',
    'enrollment_fee_adjustments',
    'enrollments',
    'students',
    'expenses',
    'batch_fees',
    'fee_types',
];

DB::statement('SET FOREIGN_KEY_CHECKS=0');

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        DB::table($table)->truncate();
        echo "cleared={$table}" . PHP_EOL;
    }
}

DB::statement('SET FOREIGN_KEY_CHECKS=1');
