<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\AdmissionRequest;
use App\Models\Batch;
use App\Models\BatchAdmissionLink;
use App\Models\BatchFee;
use App\Models\Distribution;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSettlement;
use App\Models\TeacherSettlementItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Services\IncomeDistributionService;
use App\Services\TeacherSettlementService;

class DemoBatchFlowSeeder extends Seeder
{
    /**
     * Seed demo users, teachers, and batches for flow testing.
     */
    public function run(): void
    {
        Payment::query()->delete();
        Expense::query()->delete();
        AdmissionRequest::query()->delete();
        BatchAdmissionLink::query()->delete();
        Enrollment::query()->delete();
        TeacherSettlementItem::query()->delete();
        TeacherSettlement::query()->delete();
        Distribution::query()->delete();

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $admin->syncRoles(['Admin']);

        $accounts = User::updateOrCreate(
            ['email' => 'accounts@example.com'],
            [
                'name' => 'Demo Accounts',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $accounts->syncRoles(['Accounts']);

        $inactiveAdmin = User::updateOrCreate(
            ['email' => 'inactive.admin@example.com'],
            [
                'name' => 'Inactive Admin',
                'password' => Hash::make('password'),
                'status' => 'inactive',
                'email_verified_at' => now(),
            ],
        );
        $inactiveAdmin->syncRoles(['Admin']);

        $inactiveAccounts = User::updateOrCreate(
            ['email' => 'inactive.accounts@example.com'],
            [
                'name' => 'Inactive Accounts',
                'password' => Hash::make('password'),
                'status' => 'inactive',
                'email_verified_at' => now(),
            ],
        );
        $inactiveAccounts->syncRoles(['Accounts']);

        $teacherUserOne = User::updateOrCreate(
            ['email' => 'teacher1@example.com'],
            [
                'name' => 'Rahim Sir',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $teacherUserOne->syncRoles(['Teacher']);

        $teacherUserTwo = User::updateOrCreate(
            ['email' => 'teacher2@example.com'],
            [
                'name' => 'Karim Sir',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $teacherUserTwo->syncRoles(['Teacher']);

        $teacherUserThree = User::updateOrCreate(
            ['email' => 'teacher3@example.com'],
            [
                'name' => 'Sultana Madam',
                'password' => Hash::make('password'),
                'status' => 'active',
                'email_verified_at' => now(),
            ],
        );
        $teacherUserThree->syncRoles(['Teacher']);

        $inactiveTeacherUser = User::updateOrCreate(
            ['email' => 'inactive.teacher@example.com'],
            [
                'name' => 'Inactive Teacher',
                'password' => Hash::make('password'),
                'status' => 'inactive',
                'email_verified_at' => now(),
            ],
        );
        $inactiveTeacherUser->syncRoles(['Teacher']);

        $teacherOne = Teacher::updateOrCreate(
            ['user_id' => $teacherUserOne->id],
            ['status' => 'active'],
        );
        $teacherTwo = Teacher::updateOrCreate(
            ['user_id' => $teacherUserTwo->id],
            ['status' => 'active'],
        );
        $teacherThree = Teacher::updateOrCreate(
            ['user_id' => $teacherUserThree->id],
            ['status' => 'active'],
        );
        Teacher::updateOrCreate(
            ['user_id' => $inactiveTeacherUser->id],
            ['status' => 'inactive'],
        );

        $classEight = AcademicClass::where('name', 'Class 8')->firstOrFail();
        $classNine = AcademicClass::where('name', 'Class 9')->firstOrFail();
        $classTen = AcademicClass::where('name', 'Class 10')->firstOrFail();

        $physics = Subject::where('name', 'Physics')->firstOrFail();
        $chemistry = Subject::where('name', 'Chemistry')->firstOrFail();
        $english = Subject::where('name', 'English')->firstOrFail();
        $mathematics = Subject::where('name', 'Mathematics')->firstOrFail();

        $physicsBatch = Batch::updateOrCreate(
            ['name' => 'Physics Batch Morning', 'class_id' => $classTen->id, 'subject_id' => $physics->id],
            [
                'monthly_fee' => 1500,
                'distribution_type' => 'single',
                'schedule_days' => ['sun', 'tue', 'thu'],
                'start_time' => '08:00',
                'end_time' => '09:30',
                'status' => 'active',
            ],
        );
        $physicsBatch->teachers()->sync([$teacherOne->id]);

        $chemistryBatch = Batch::updateOrCreate(
            ['name' => 'Chemistry Batch Evening', 'class_id' => $classNine->id, 'subject_id' => $chemistry->id],
            [
                'monthly_fee' => 1400,
                'distribution_type' => 'single',
                'schedule_days' => ['sat', 'mon', 'wed'],
                'start_time' => '18:30',
                'end_time' => '20:00',
                'status' => 'active',
            ],
        );
        $chemistryBatch->teachers()->sync([$teacherTwo->id]);

        $classEightCombined = Batch::updateOrCreate(
            ['name' => 'Class 8 Batch A', 'class_id' => $classEight->id, 'subject_id' => null],
            [
                'monthly_fee' => 2200,
                'distribution_type' => 'equal',
                'schedule_days' => ['sat', 'sun', 'tue'],
                'start_time' => '16:00',
                'end_time' => '18:00',
                'status' => 'active',
            ],
        );
        $classEightCombined->teachers()->sync([$teacherOne->id, $teacherTwo->id, $teacherThree->id]);

        $classEightEnglish = Batch::updateOrCreate(
            ['name' => 'Class 8 English Evening', 'class_id' => $classEight->id, 'subject_id' => $english->id],
            [
                'monthly_fee' => 1300,
                'distribution_type' => 'single',
                'schedule_days' => ['mon', 'wed'],
                'start_time' => '17:00',
                'end_time' => '18:15',
                'status' => 'active',
            ],
        );
        $classEightEnglish->teachers()->sync([$teacherTwo->id]);

        $mathBatch = Batch::updateOrCreate(
            ['name' => 'Mathematics Weekend', 'class_id' => $classTen->id, 'subject_id' => $mathematics->id],
            [
                'monthly_fee' => 1600,
                'distribution_type' => 'single',
                'schedule_days' => ['fri'],
                'start_time' => '10:00',
                'end_time' => '11:30',
                'status' => 'inactive',
            ],
        );
        $mathBatch->teachers()->sync([$teacherThree->id]);

        Student::updateOrCreate(
            ['student_code' => 'STD0001'],
            [
                'name' => 'Nusrat Jahan',
                'class_id' => $classEight->id,
                'phone' => '01710000001',
                'guardian_phone' => '01810000001',
                'school' => 'City Girls School',
                'address' => 'Uttara, Dhaka',
                'status' => 'active',
            ],
        );

        Student::updateOrCreate(
            ['student_code' => 'STD0002'],
            [
                'name' => 'Siam Ahmed',
                'class_id' => $classNine->id,
                'phone' => '01710000002',
                'guardian_phone' => '01810000002',
                'school' => 'Ideal High School',
                'address' => 'Mirpur, Dhaka',
                'status' => 'active',
            ],
        );

        Student::updateOrCreate(
            ['student_code' => 'STD0003'],
            [
                'name' => 'Tasnim Rahman',
                'class_id' => $classTen->id,
                'phone' => null,
                'guardian_phone' => '01810000003',
                'school' => 'Model School and College',
                'address' => 'Mohammadpur, Dhaka',
                'status' => 'inactive',
            ],
        );

        $studentOne = Student::where('student_code', 'STD0001')->firstOrFail();
        $studentTwo = Student::where('student_code', 'STD0002')->firstOrFail();
        $studentThree = Student::where('student_code', 'STD0003')->firstOrFail();
        $superAdmin = User::where('email', 'superadmin@example.com')->firstOrFail();

        Enrollment::updateOrCreate(
            ['student_id' => $studentOne->id, 'batch_id' => $classEightCombined->id, 'start_date' => '2026-04-01'],
            [
                'end_date' => null,
                'status' => 'active',
                'created_by' => $superAdmin->id,
            ],
        );

        Enrollment::updateOrCreate(
            ['student_id' => $studentOne->id, 'batch_id' => $classEightEnglish->id, 'start_date' => '2026-04-10'],
            [
                'end_date' => null,
                'status' => 'active',
                'created_by' => $superAdmin->id,
            ],
        );

        Enrollment::updateOrCreate(
            ['student_id' => $studentTwo->id, 'batch_id' => $chemistryBatch->id, 'start_date' => '2026-04-05'],
            [
                'end_date' => null,
                'status' => 'active',
                'created_by' => $superAdmin->id,
            ],
        );

        Enrollment::updateOrCreate(
            ['student_id' => $studentThree->id, 'batch_id' => $physicsBatch->id, 'start_date' => '2026-03-01'],
            [
                'end_date' => '2026-03-28',
                'status' => 'withdrawn',
                'created_by' => $superAdmin->id,
            ],
        );

        $classEightLink = BatchAdmissionLink::updateOrCreate(
            ['token' => 'demo-class-8-batch-a-link'],
            [
                'batch_id' => $classEightCombined->id,
                'title' => 'Class 8 WhatsApp Admission Link',
                'status' => 'active',
                'expires_at' => now()->addMonth(),
                'created_by' => $superAdmin->id,
            ],
        );

        AdmissionRequest::updateOrCreate(
            ['batch_admission_link_id' => $classEightLink->id, 'guardian_phone' => '01810000011', 'name' => 'Farhan Hossain'],
            [
                'batch_id' => $classEightCombined->id,
                'student_id' => null,
                'phone' => '01710000011',
                'school' => 'Scholars School',
                'address' => 'Uttara, Dhaka',
                'photo_path' => null,
                'status' => 'pending',
                'review_note' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ],
        );

        $studentOneCombinedEnrollment = Enrollment::where('student_id', $studentOne->id)
            ->where('batch_id', $classEightCombined->id)
            ->firstOrFail();

        $studentOneEnglishEnrollment = Enrollment::where('student_id', $studentOne->id)
            ->where('batch_id', $classEightEnglish->id)
            ->firstOrFail();

        $studentTwoChemistryEnrollment = Enrollment::where('student_id', $studentTwo->id)
            ->where('batch_id', $chemistryBatch->id)
            ->firstOrFail();

        $tuitionFeeType = FeeType::firstOrCreate(
            ['code' => 'monthly_tuition'],
            ['name' => 'Monthly Tuition Fee', 'frequency' => 'monthly', 'status' => 'active'],
        );
        $admissionFeeType = FeeType::firstOrCreate(
            ['code' => 'admission_fee'],
            ['name' => 'Admission Fee', 'frequency' => 'one_time', 'status' => 'active'],
        );
        $examFeeType = FeeType::firstOrCreate(
            ['code' => 'exam_fee'],
            ['name' => 'Exam Fee', 'frequency' => 'manual', 'status' => 'active'],
        );

        $classEightTuitionFee = BatchFee::updateOrCreate(
            ['batch_id' => $classEightCombined->id, 'fee_type_id' => $tuitionFeeType->id],
            ['amount' => 2200, 'status' => 'active'],
        );
        $classEightAdmissionFee = BatchFee::updateOrCreate(
            ['batch_id' => $classEightCombined->id, 'fee_type_id' => $admissionFeeType->id],
            ['amount' => 1000, 'status' => 'active'],
        );
        $classEightExamFee = BatchFee::updateOrCreate(
            ['batch_id' => $classEightCombined->id, 'fee_type_id' => $examFeeType->id],
            ['amount' => 600, 'status' => 'active'],
        );
        $classEightEnglishTuitionFee = BatchFee::updateOrCreate(
            ['batch_id' => $classEightEnglish->id, 'fee_type_id' => $tuitionFeeType->id],
            ['amount' => 1300, 'status' => 'active'],
        );
        $chemistryTuitionFee = BatchFee::updateOrCreate(
            ['batch_id' => $chemistryBatch->id, 'fee_type_id' => $tuitionFeeType->id],
            ['amount' => 1400, 'status' => 'active'],
        );

        Payment::where('enrollment_id', $studentTwoChemistryEnrollment->id)
            ->where('month', '2026-04')
            ->delete();

        Payment::updateOrCreate(
            ['enrollment_id' => $studentOneCombinedEnrollment->id, 'month' => '2026-04', 'transaction_id' => null],
            [
                'batch_fee_id' => $classEightTuitionFee->id,
                'amount' => 2200,
                'payment_date' => '2026-04-07',
                'method' => 'cash',
                'transaction_id' => null,
                'status' => 'approved',
                'collected_by' => $admin->id,
                'approved_by' => $admin->id,
            ],
        );

        Payment::updateOrCreate(
            ['enrollment_id' => $studentOneEnglishEnrollment->id, 'month' => '2026-04', 'transaction_id' => 'BKASH-APR-1001'],
            [
                'batch_fee_id' => $classEightEnglishTuitionFee->id,
                'amount' => 1300,
                'payment_date' => '2026-04-11',
                'method' => 'bkash',
                'transaction_id' => 'BKASH-APR-1001',
                'status' => 'pending',
                'collected_by' => $accounts->id,
                'approved_by' => null,
            ],
        );

        Payment::updateOrCreate(
            ['enrollment_id' => $studentTwoChemistryEnrollment->id, 'month' => '2026-04', 'transaction_id' => 'CASH-APR-3001'],
            [
                'batch_fee_id' => $chemistryTuitionFee->id,
                'amount' => 800,
                'payment_date' => '2026-04-09',
                'method' => 'cash',
                'status' => 'approved',
                'collected_by' => $teacherUserTwo->id,
                'approved_by' => $teacherUserTwo->id,
            ],
        );

        Payment::updateOrCreate(
            ['enrollment_id' => $studentTwoChemistryEnrollment->id, 'month' => '2026-04', 'transaction_id' => 'NAGAD-APR-3002'],
            [
                'batch_fee_id' => $chemistryTuitionFee->id,
                'amount' => 400,
                'payment_date' => '2026-04-12',
                'method' => 'nagad',
                'status' => 'pending',
                'collected_by' => $accounts->id,
                'approved_by' => null,
            ],
        );

        Payment::updateOrCreate(
            ['enrollment_id' => $studentTwoChemistryEnrollment->id, 'month' => '2026-03', 'transaction_id' => 'NAGAD-MAR-2001'],
            [
                'batch_fee_id' => $chemistryTuitionFee->id,
                'amount' => 1400,
                'payment_date' => '2026-03-08',
                'method' => 'nagad',
                'transaction_id' => 'NAGAD-MAR-2001',
                'status' => 'approved',
                'collected_by' => $accounts->id,
                'approved_by' => $admin->id,
            ],
        );

        Payment::updateOrCreate(
            ['enrollment_id' => $studentOneCombinedEnrollment->id, 'batch_fee_id' => $classEightAdmissionFee->id, 'transaction_id' => 'CASH-ADM-0001'],
            [
                'month' => null,
                'amount' => 1000,
                'payment_date' => '2026-04-01',
                'method' => 'cash',
                'status' => 'approved',
                'collected_by' => $admin->id,
                'approved_by' => $admin->id,
            ],
        );

        Payment::updateOrCreate(
            ['enrollment_id' => $studentOneCombinedEnrollment->id, 'batch_fee_id' => $classEightExamFee->id, 'transaction_id' => 'NAGAD-EXAM-0001'],
            [
                'month' => null,
                'amount' => 300,
                'payment_date' => '2026-04-15',
                'method' => 'nagad',
                'status' => 'pending',
                'collected_by' => $accounts->id,
                'approved_by' => null,
            ],
        );

        $distributionService = app(IncomeDistributionService::class);

        Payment::query()
            ->where('status', 'approved')
            ->each(fn (Payment $payment) => $distributionService->distribute($payment));

        $settlementService = app(TeacherSettlementService::class);

        $settlementService->settle(
            $teacherTwo,
            1500.00,
            '2026-04-20',
            $admin->id,
            'Partial settlement to Karim Sir against collected liabilities.',
        );

        $settlementService->settle(
            $teacherOne,
            500.00,
            '2026-04-21',
            $accounts->id,
            'Partial settlement to Rahim Sir after admin-held collections.',
        );

        Expense::updateOrCreate(
            ['type' => 'common', 'expense_date' => '2026-04-01', 'note' => 'Office rent for April'],
            [
                'teacher_id' => null,
                'amount' => 12000,
                'created_by' => $admin->id,
            ],
        );

        Expense::updateOrCreate(
            ['type' => 'common', 'expense_date' => '2026-04-04', 'note' => 'Internet and electricity bill'],
            [
                'teacher_id' => null,
                'amount' => 3200,
                'created_by' => $accounts->id,
            ],
        );

        Expense::updateOrCreate(
            ['type' => 'teacher', 'expense_date' => '2026-04-10', 'note' => 'Chemistry teacher class notes support'],
            [
                'teacher_id' => $teacherTwo->id,
                'amount' => 900,
                'created_by' => $accounts->id,
            ],
        );

        Expense::updateOrCreate(
            ['type' => 'teacher', 'expense_date' => '2026-04-12', 'note' => 'Combined batch whiteboard marker and print support'],
            [
                'teacher_id' => $teacherOne->id,
                'amount' => 650,
                'created_by' => $admin->id,
            ],
        );
    }
}
