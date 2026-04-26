<?php

use App\Http\Controllers\Admin\BatchController;
use App\Http\Controllers\Admin\BatchAdmissionLinkController;
use App\Http\Controllers\Admin\BatchFeeController;
use App\Http\Controllers\Admin\AcademicClassController;
use App\Http\Controllers\Admin\AdmissionRequestController;
use App\Http\Controllers\Admin\DistributionController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\FeeTypeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Admin\TeacherSettlementController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicAdmissionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/apply/{token}', [PublicAdmissionController::class, 'create'])->name('admission.apply');
Route::post('/apply/{token}', [PublicAdmissionController::class, 'store'])->name('admission.submit');

Route::get('/dashboard', [DashboardController::class, 'redirect'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth', 'verified', 'role:Super Admin'])->group(function () {
    Route::get('/dashboard/super-admin', [DashboardController::class, 'superAdmin'])->name('dashboard.super-admin');
});

Route::middleware(['auth', 'verified', 'role:Admin'])->group(function () {
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->name('dashboard.admin');
});

Route::middleware(['auth', 'verified', 'role:Teacher'])->group(function () {
    Route::get('/dashboard/teacher', [DashboardController::class, 'teacher'])->name('dashboard.teacher');
});

Route::middleware(['auth', 'verified', 'role:Accounts'])->group(function () {
    Route::get('/dashboard/accounts', [DashboardController::class, 'accounts'])->name('dashboard.accounts');
});

Route::middleware(['auth', 'role:Super Admin|Admin'])->group(function () {
    Route::resource('/admin/classes', AcademicClassController::class)
        ->except(['show', 'destroy'])
        ->parameters(['classes' => 'class'])
        ->names('admin.classes');

    Route::resource('/admin/subjects', SubjectController::class)
        ->except(['show', 'destroy'])
        ->names('admin.subjects');

    Route::resource('/admin/teachers', TeacherController::class)
        ->except(['show', 'destroy'])
        ->names('admin.teachers');
});

Route::middleware(['auth', 'permission:manage students'])->group(function () {
    Route::resource('/admin/students', StudentController::class)
        ->except(['destroy'])
        ->names('admin.students');
});

Route::middleware(['auth', 'role_or_permission:Teacher|manage students|collect payments|manage enrollments'])->group(function () {
    Route::get('/admin/student-profiles/{student}', [StudentController::class, 'show'])->name('admin.student-profiles.show');
    Route::get('/admin/student-lookup', [StudentController::class, 'lookup'])->name('admin.student-lookup.index');
});

Route::middleware(['auth', 'role_or_permission:Teacher|manage enrollments'])->group(function () {
    Route::get('/admin/enrollments', [EnrollmentController::class, 'index'])->name('admin.enrollments.index');
    Route::get('/admin/enrollments/create', [EnrollmentController::class, 'create'])->name('admin.enrollments.create');
    Route::post('/admin/enrollments', [EnrollmentController::class, 'store'])->name('admin.enrollments.store');
    Route::get('/admin/enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('admin.enrollments.show');
    Route::get('/admin/enrollments/{enrollment}/withdraw', [EnrollmentController::class, 'withdrawForm'])->name('admin.enrollments.withdraw.form');
    Route::patch('/admin/enrollments/{enrollment}/withdraw', [EnrollmentController::class, 'withdraw'])->name('admin.enrollments.withdraw');
});

Route::middleware(['auth', 'permission:manage enrollments'])->group(function () {
    Route::get('/admin/admission-links', [BatchAdmissionLinkController::class, 'index'])->name('admin.admission-links.index');
    Route::get('/admin/admission-links/create', [BatchAdmissionLinkController::class, 'create'])->name('admin.admission-links.create');
    Route::post('/admin/admission-links', [BatchAdmissionLinkController::class, 'store'])->name('admin.admission-links.store');
    Route::get('/admin/admission-links/{admissionLink}', [BatchAdmissionLinkController::class, 'show'])->name('admin.admission-links.show');

    Route::get('/admin/admission-requests', [AdmissionRequestController::class, 'index'])->name('admin.admission-requests.index');
    Route::get('/admin/admission-requests/{admissionRequest}', [AdmissionRequestController::class, 'show'])->name('admin.admission-requests.show');
    Route::patch('/admin/admission-requests/{admissionRequest}/approve', [AdmissionRequestController::class, 'approve'])->name('admin.admission-requests.approve');
    Route::patch('/admin/admission-requests/{admissionRequest}/reject', [AdmissionRequestController::class, 'reject'])->name('admin.admission-requests.reject');
});

Route::middleware(['auth', 'permission:manage fee setup'])->group(function () {
    Route::resource('/admin/fee-types', FeeTypeController::class)
        ->except(['show', 'destroy'])
        ->names('admin.fee-types');
    Route::get('/admin/batches/{batch}/fees', [BatchFeeController::class, 'index'])->name('admin.batch-fees.index');
    Route::post('/admin/batches/{batch}/fees', [BatchFeeController::class, 'store'])->name('admin.batch-fees.store');
    Route::put('/admin/batches/{batch}/fees/{batch_fee}', [BatchFeeController::class, 'update'])->name('admin.batch-fees.update');
});

Route::middleware(['auth', 'permission:collect payments'])->group(function () {
    Route::get('/admin/payments', [PaymentController::class, 'index'])->name('admin.payments.index');
    Route::get('/admin/payments/create', [PaymentController::class, 'create'])->name('admin.payments.create');
    Route::post('/admin/payments', [PaymentController::class, 'store'])->name('admin.payments.store');
    Route::get('/admin/payments/due-list', [PaymentController::class, 'dueList'])->name('admin.payments.due-list');
});

Route::middleware(['auth', 'permission:approve payments'])->group(function () {
    Route::patch('/admin/payments/{payment}/approve', [PaymentController::class, 'approve'])->name('admin.payments.approve');
    Route::patch('/admin/payments/{payment}/reject', [PaymentController::class, 'reject'])->name('admin.payments.reject');
});

Route::middleware(['auth', 'role_or_permission:Teacher|approve payments|settle teacher payments'])->group(function () {
    Route::get('/admin/distributions', [DistributionController::class, 'index'])->name('admin.distributions.index');
    Route::get('/admin/teacher-settlements', [TeacherSettlementController::class, 'index'])->name('admin.teacher-settlements.index');
});

Route::middleware(['auth', 'permission:settle teacher payments'])->group(function () {
    Route::get('/admin/teacher-settlements/create', [TeacherSettlementController::class, 'create'])->name('admin.teacher-settlements.create');
    Route::post('/admin/teacher-settlements', [TeacherSettlementController::class, 'store'])->name('admin.teacher-settlements.store');
});

Route::middleware(['auth', 'permission:manage expenses'])->group(function () {
    Route::resource('/admin/expenses', ExpenseController::class)
        ->except(['show'])
        ->names('admin.expenses');
});

Route::middleware(['auth', 'role:Teacher'])->group(function () {
    Route::get('/teacher/earnings', [DistributionController::class, 'index'])->name('teacher.earnings.index');
    Route::get('/teacher/settlements', [TeacherSettlementController::class, 'index'])->name('teacher.settlements.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:Super Admin|Admin'])->group(function () {
    Route::view('/admin/rbac-demo', 'admin.rbac-demo')->name('admin.rbac-demo');
});

Route::middleware(['auth', 'permission:manage users'])->group(function () {
    Route::resource('/admin/users', UserController::class)
        ->except(['show', 'destroy'])
        ->names('admin.users');
});

Route::middleware(['auth', 'role_or_permission:Teacher|manage batches'])->group(function () {
    Route::resource('/admin/batches', BatchController::class)
        ->except(['destroy'])
        ->names('admin.batches');
});

Route::middleware(['auth', 'role_or_permission:Teacher|view reports'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/students', [ReportController::class, 'students'])->name('reports.students');
    Route::get('/reports/dues', [ReportController::class, 'dues'])->name('reports.dues');
    Route::get('/reports/collections', [ReportController::class, 'collections'])->name('reports.collections');
    Route::get('/reports/enrollments', [ReportController::class, 'enrollments'])->name('reports.enrollments');
    Route::get('/reports/teacher-finance', [ReportController::class, 'teacherFinance'])->name('reports.teacher-finance');
    Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
});

require __DIR__.'/auth.php';
