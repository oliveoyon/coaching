<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BatchScheduleController;
use App\Http\Controllers\FeeHeadController;
use App\Http\Controllers\FeeStructureController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentReceiptController;
use App\Http\Controllers\StudentDueController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentEnrollmentController;
use App\Http\Controllers\StudentFeeOverrideController;
use App\Http\Controllers\TenantBillingConfigController;
use App\Http\Controllers\TenantSettingsController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'tenant'])
    ->name('dashboard');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/settings', [TenantSettingsController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/profile', [TenantSettingsController::class, 'updateProfile'])->name('settings.profile.update');
    Route::put('/settings/billing', [TenantSettingsController::class, 'updateBilling'])->name('settings.billing.update');
    Route::put('/settings/communication', [TenantSettingsController::class, 'updateCommunication'])->name('settings.communication.update');
    Route::put('/settings/post-payment', [TenantSettingsController::class, 'updatePostPayment'])->name('settings.post-payment.update');
    Route::get('/billing-settings', [TenantBillingConfigController::class, 'edit'])->name('billing-settings.edit');
    Route::put('/billing-settings', [TenantBillingConfigController::class, 'update'])->name('billing-settings.update');
    Route::resource('fee-heads', FeeHeadController::class)->except('show');
    Route::resource('fee-structures', FeeStructureController::class)->except('show');
    Route::resource('student-fee-overrides', StudentFeeOverrideController::class)->except('show');
    Route::resource('schedules', BatchScheduleController::class)->except('show');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/create', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/dues', [StudentDueController::class, 'index'])->name('dues.index');
    Route::post('/dues/generate', [StudentDueController::class, 'generate'])->name('dues.generate');
    Route::get('/dues/{due}', [StudentDueController::class, 'show'])->name('dues.show');
    Route::get('/payments/{payment}/receipts/{template}', [PaymentReceiptController::class, 'show'])->name('payments.receipts.show');
    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'show']);
    Route::resource('teachers', TeacherController::class)->except('show');
    Route::resource('students', StudentController::class)->except('show');
    Route::resource('enrollments', StudentEnrollmentController::class)->except('show');
    Route::resource('batches', BatchController::class)->except('show');
});

require __DIR__.'/auth.php';
