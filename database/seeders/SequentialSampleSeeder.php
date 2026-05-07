<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\BatchFee;
use App\Models\Enrollment;
use App\Models\Expense;
use App\Models\FeeType;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherSettlement;
use App\Models\User;
use App\Services\IncomeDistributionService;
use App\Services\TeacherSettlementService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class SequentialSampleSeeder extends Seeder
{
    /**
     * Safe sample seed order:
     * roles -> users -> teachers -> classes -> subjects -> batches -> fees
     * -> students -> enrollments -> payments -> settlements -> expenses
     *
     * Replace the sample arrays with your real data later.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        $users = $this->seedUsers();
        $teachers = $this->seedTeachers();
        $classes = $this->seedClasses();
        $subjects = $this->seedSubjects();
        $batches = $this->seedBatches($classes, $subjects, $teachers);
        $feeTypes = $this->seedFeeTypes();
        $this->seedBatchFees($batches, $feeTypes);
        $students = $this->seedStudents($classes);
        $enrollments = $this->seedEnrollments($students, $batches, $users);
        $this->seedPayments($enrollments, $feeTypes, $users);
        $this->seedTeacherSettlements($teachers, $users);
        $this->seedExpenses($teachers, $users);
    }

    /**
     * @return array<string, User>
     */
    protected function seedUsers(): array
    {
        $rows = [
            [
                'key' => 'admin',
                'name' => 'Main Admin',
                'username' => 'mainadmin',
                'email' => 'admin@coaching.test',
                'password' => 'password',
                'role' => 'Admin',
                'status' => 'active',
            ],
            [
                'key' => 'accounts',
                'name' => 'Accounts Officer',
                'username' => 'accounts',
                'email' => 'accounts@coaching.test',
                'password' => 'password',
                'role' => 'Accounts',
                'status' => 'active',
            ],
            [
                'key' => 'rahim',
                'name' => 'Rahim Sir',
                'username' => 'rahim',
                'email' => 'rahim@coaching.test',
                'password' => 'password',
                'role' => 'Teacher',
                'status' => 'active',
            ],
            [
                'key' => 'karim',
                'name' => 'Karim Sir',
                'username' => 'karim',
                'email' => 'karim@coaching.test',
                'password' => 'password',
                'role' => 'Teacher',
                'status' => 'active',
            ],
        ];

        $users = [];

        foreach ($rows as $row) {
            $user = User::updateOrCreate(
                ['email' => Str::lower($row['email'])],
                [
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'password' => Hash::make($row['password']),
                    'status' => $row['status'],
                    'email_verified_at' => now(),
                ]
            );

            if (Role::query()->where('name', $row['role'])->exists()) {
                $user->syncRoles([$row['role']]);
            }

            $users[$row['key']] = $user;
        }

        return $users;
    }

    /**
     * @return array<string, Teacher>
     */
    protected function seedTeachers(): array
    {
        $rows = [
            ['key' => 'rahim', 'user_email' => 'rahim@coaching.test', 'status' => 'active'],
            ['key' => 'karim', 'user_email' => 'karim@coaching.test', 'status' => 'active'],
        ];

        $teachers = [];

        foreach ($rows as $row) {
            $user = User::query()->where('email', Str::lower($row['user_email']))->firstOrFail();
            $user->syncRoles(['Teacher']);

            $teachers[$row['key']] = Teacher::updateOrCreate(
                ['user_id' => $user->id],
                ['status' => $row['status']]
            );
        }

        return $teachers;
    }

    /**
     * @return array<string, AcademicClass>
     */
    protected function seedClasses(): array
    {
        $rows = [
            ['key' => 'class_8', 'name' => 'Class 8', 'status' => 'active'],
            ['key' => 'class_10', 'name' => 'Class 10', 'status' => 'active'],
        ];

        $classes = [];

        foreach ($rows as $row) {
            $classes[$row['key']] = AcademicClass::updateOrCreate(
                ['name' => $row['name']],
                ['status' => $row['status']]
            );
        }

        return $classes;
    }

    /**
     * @return array<string, Subject>
     */
    protected function seedSubjects(): array
    {
        $rows = [
            ['key' => 'english', 'name' => 'English', 'status' => 'active'],
            ['key' => 'physics', 'name' => 'Physics', 'status' => 'active'],
        ];

        $subjects = [];

        foreach ($rows as $row) {
            $subjects[$row['key']] = Subject::updateOrCreate(
                ['name' => $row['name']],
                ['status' => $row['status']]
            );
        }

        return $subjects;
    }

    /**
     * @param  array<string, AcademicClass>  $classes
     * @param  array<string, Subject>  $subjects
     * @param  array<string, Teacher>  $teachers
     * @return array<string, Batch>
     */
    protected function seedBatches(array $classes, array $subjects, array $teachers): array
    {
        $rows = [
            [
                'key' => 'class_8_batch_a',
                'name' => 'Class 8 Batch A',
                'class_key' => 'class_8',
                'subject_key' => null,
                'monthly_fee' => 2200,
                'distribution_type' => 'equal',
                'status' => 'active',
                'schedule_slots' => [
                    ['day' => 'sun', 'start_time' => '16:00', 'end_time' => '17:30'],
                    ['day' => 'tue', 'start_time' => '17:00', 'end_time' => '18:30'],
                    ['day' => 'thu', 'start_time' => '16:00', 'end_time' => '17:30'],
                ],
                'teacher_keys' => ['rahim', 'karim'],
            ],
            [
                'key' => 'physics_morning',
                'name' => 'Physics Morning',
                'class_key' => 'class_10',
                'subject_key' => 'physics',
                'monthly_fee' => 1500,
                'distribution_type' => 'single',
                'status' => 'active',
                'schedule_slots' => [
                    ['day' => 'sat', 'start_time' => '08:00', 'end_time' => '09:30'],
                    ['day' => 'mon', 'start_time' => '08:00', 'end_time' => '09:30'],
                ],
                'teacher_keys' => ['rahim'],
            ],
        ];

        $batches = [];

        foreach ($rows as $row) {
            $subjectId = $row['subject_key'] ? $subjects[$row['subject_key']]->id : null;
            $slots = collect($row['schedule_slots']);

            $batch = Batch::updateOrCreate(
                [
                    'name' => $row['name'],
                    'class_id' => $classes[$row['class_key']]->id,
                    'subject_id' => $subjectId,
                ],
                [
                    'monthly_fee' => $row['monthly_fee'],
                    'distribution_type' => $row['distribution_type'],
                    'status' => $row['status'],
                    'schedule_slots' => $row['schedule_slots'],
                    'schedule_days' => $slots->pluck('day')->unique()->values()->all(),
                    'start_time' => $slots->first()['start_time'] ?? null,
                    'end_time' => $slots->first()['end_time'] ?? null,
                ]
            );

            $batch->teachers()->sync(
                collect($row['teacher_keys'])->map(fn ($key) => $teachers[$key]->id)->all()
            );

            $batches[$row['key']] = $batch;
        }

        return $batches;
    }

    /**
     * @return array<string, FeeType>
     */
    protected function seedFeeTypes(): array
    {
        $rows = [
            ['key' => 'admission', 'name' => 'Admission Fee', 'code' => 'ADM', 'frequency' => 'one_time', 'status' => 'active'],
            ['key' => 'tuition', 'name' => 'Tuition Fee', 'code' => 'TUI', 'frequency' => 'monthly', 'status' => 'active'],
            ['key' => 'exam', 'name' => 'Exam Fee', 'code' => 'EXM', 'frequency' => 'manual', 'status' => 'active'],
        ];

        $feeTypes = [];

        foreach ($rows as $row) {
            $feeTypes[$row['key']] = FeeType::updateOrCreate(
                ['code' => $row['code']],
                [
                    'name' => $row['name'],
                    'frequency' => $row['frequency'],
                    'status' => $row['status'],
                ]
            );
        }

        return $feeTypes;
    }

    /**
     * @param  array<string, Batch>  $batches
     * @param  array<string, FeeType>  $feeTypes
     */
    protected function seedBatchFees(array $batches, array $feeTypes): void
    {
        $rows = [
            ['batch_key' => 'class_8_batch_a', 'fee_type_key' => 'admission', 'amount' => 1000, 'status' => 'active'],
            ['batch_key' => 'class_8_batch_a', 'fee_type_key' => 'tuition', 'amount' => 2200, 'status' => 'active'],
            ['batch_key' => 'class_8_batch_a', 'fee_type_key' => 'exam', 'amount' => 500, 'status' => 'active'],
            ['batch_key' => 'physics_morning', 'fee_type_key' => 'admission', 'amount' => 800, 'status' => 'active'],
            ['batch_key' => 'physics_morning', 'fee_type_key' => 'tuition', 'amount' => 1500, 'status' => 'active'],
        ];

        foreach ($rows as $row) {
            BatchFee::updateOrCreate(
                [
                    'batch_id' => $batches[$row['batch_key']]->id,
                    'fee_type_id' => $feeTypes[$row['fee_type_key']]->id,
                ],
                [
                    'amount' => $row['amount'],
                    'status' => $row['status'],
                ]
            );
        }
    }

    /**
     * @param  array<string, AcademicClass>  $classes
     * @return array<string, Student>
     */
    protected function seedStudents(array $classes): array
    {
        $rows = [
            [
                'key' => 'nusrat',
                'student_code' => 'STD1001',
                'name' => 'Nusrat Jahan',
                'class_key' => 'class_8',
                'phone' => '01710000001',
                'guardian_phone' => '01810000001',
                'school' => 'City Girls School',
                'address' => 'Uttara Dhaka',
                'status' => 'active',
            ],
            [
                'key' => 'siam',
                'student_code' => 'STD1002',
                'name' => 'Siam Ahmed',
                'class_key' => 'class_10',
                'phone' => '01710000002',
                'guardian_phone' => '01810000002',
                'school' => 'Model School',
                'address' => 'Mirpur Dhaka',
                'status' => 'active',
            ],
        ];

        $students = [];

        foreach ($rows as $row) {
            $students[$row['key']] = Student::updateOrCreate(
                ['student_code' => $row['student_code']],
                [
                    'name' => $row['name'],
                    'class_id' => $classes[$row['class_key']]->id,
                    'phone' => $row['phone'],
                    'guardian_phone' => $row['guardian_phone'],
                    'school' => $row['school'],
                    'address' => $row['address'],
                    'status' => $row['status'],
                ]
            );
        }

        return $students;
    }

    /**
     * @param  array<string, Student>  $students
     * @param  array<string, Batch>  $batches
     * @param  array<string, User>  $users
     * @return array<string, Enrollment>
     */
    protected function seedEnrollments(array $students, array $batches, array $users): array
    {
        $rows = [
            [
                'key' => 'nusrat_batch_a',
                'student_key' => 'nusrat',
                'batch_key' => 'class_8_batch_a',
                'start_date' => '2026-04-01',
                'end_date' => null,
                'status' => 'active',
                'created_by_key' => 'admin',
            ],
            [
                'key' => 'siam_physics',
                'student_key' => 'siam',
                'batch_key' => 'physics_morning',
                'start_date' => '2026-05-01',
                'end_date' => null,
                'status' => 'active',
                'created_by_key' => 'admin',
            ],
        ];

        $enrollments = [];

        foreach ($rows as $row) {
            $enrollments[$row['key']] = Enrollment::updateOrCreate(
                [
                    'student_id' => $students[$row['student_key']]->id,
                    'batch_id' => $batches[$row['batch_key']]->id,
                    'start_date' => $row['start_date'],
                ],
                [
                    'end_date' => $row['end_date'],
                    'status' => $row['status'],
                    'created_by' => $users[$row['created_by_key']]->id,
                ]
            );
        }

        return $enrollments;
    }

    /**
     * @param  array<string, Enrollment>  $enrollments
     * @param  array<string, FeeType>  $feeTypes
     * @param  array<string, User>  $users
     */
    protected function seedPayments(array $enrollments, array $feeTypes, array $users): void
    {
        $distributionService = app(IncomeDistributionService::class);

        $rows = [
            [
                'enrollment_key' => 'nusrat_batch_a',
                'fee_type_key' => 'admission',
                'month' => null,
                'amount' => 1000,
                'payment_date' => '2026-04-01',
                'method' => 'cash',
                'transaction_id' => null,
                'status' => 'approved',
                'collected_by_key' => 'admin',
                'approved_by_key' => 'admin',
            ],
            [
                'enrollment_key' => 'nusrat_batch_a',
                'fee_type_key' => 'tuition',
                'month' => '2026-04',
                'amount' => 2200,
                'payment_date' => '2026-04-05',
                'method' => 'bkash',
                'transaction_id' => 'BKX12345',
                'status' => 'pending',
                'collected_by_key' => 'accounts',
                'approved_by_key' => null,
            ],
            [
                'enrollment_key' => 'siam_physics',
                'fee_type_key' => 'admission',
                'month' => null,
                'amount' => 800,
                'payment_date' => '2026-05-01',
                'method' => 'cash',
                'transaction_id' => null,
                'status' => 'approved',
                'collected_by_key' => 'admin',
                'approved_by_key' => 'admin',
            ],
            [
                'enrollment_key' => 'siam_physics',
                'fee_type_key' => 'tuition',
                'month' => '2026-05',
                'amount' => 1500,
                'payment_date' => '2026-05-03',
                'method' => 'cash',
                'transaction_id' => null,
                'status' => 'approved',
                'collected_by_key' => 'rahim',
                'approved_by_key' => 'rahim',
            ],
        ];

        foreach ($rows as $row) {
            $enrollment = $enrollments[$row['enrollment_key']];
            $batchFee = BatchFee::query()
                ->where('batch_id', $enrollment->batch_id)
                ->where('fee_type_id', $feeTypes[$row['fee_type_key']]->id)
                ->firstOrFail();

            $payment = Payment::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'batch_fee_id' => $batchFee->id,
                    'month' => $row['month'],
                    'payment_date' => $row['payment_date'],
                    'method' => $row['method'],
                    'transaction_id' => $row['transaction_id'],
                ],
                [
                    'amount' => $row['amount'],
                    'status' => $row['status'],
                    'collected_by' => $users[$row['collected_by_key']]->id,
                    'approved_by' => $row['approved_by_key'] ? $users[$row['approved_by_key']]->id : null,
                ]
            );

            if ($payment->status === 'approved') {
                $distributionService->distribute($payment);
            }
        }
    }

    /**
     * @param  array<string, Teacher>  $teachers
     * @param  array<string, User>  $users
     */
    protected function seedTeacherSettlements(array $teachers, array $users): void
    {
        $rows = [
            [
                'teacher_key' => 'rahim',
                'amount' => 1000,
                'settlement_date' => '2026-05-05',
                'paid_by_key' => 'accounts',
                'note' => 'Part payment for collected classes',
            ],
            [
                'teacher_key' => 'karim',
                'amount' => 500,
                'settlement_date' => '2026-05-05',
                'paid_by_key' => 'accounts',
                'note' => 'Equal share payout',
            ],
        ];

        $settlementService = app(TeacherSettlementService::class);

        foreach ($rows as $row) {
            $alreadyExists = TeacherSettlement::query()
                ->where('teacher_id', $teachers[$row['teacher_key']]->id)
                ->where('amount', $row['amount'])
                ->whereDate('settlement_date', $row['settlement_date'])
                ->where('paid_by', $users[$row['paid_by_key']]->id)
                ->where('note', $row['note'])
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $settlementService->settle(
                $teachers[$row['teacher_key']],
                (float) $row['amount'],
                $row['settlement_date'],
                $users[$row['paid_by_key']]->id,
                $row['note'],
            );
        }
    }

    /**
     * @param  array<string, Teacher>  $teachers
     * @param  array<string, User>  $users
     */
    protected function seedExpenses(array $teachers, array $users): void
    {
        $rows = [
            [
                'type' => 'common',
                'teacher_key' => null,
                'amount' => 12000,
                'expense_date' => '2026-05-01',
                'note' => 'Monthly rent',
                'created_by_key' => 'accounts',
            ],
            [
                'type' => 'teacher',
                'teacher_key' => 'rahim',
                'amount' => 600,
                'expense_date' => '2026-05-04',
                'note' => 'Physics notes printing',
                'created_by_key' => 'accounts',
            ],
        ];

        foreach ($rows as $row) {
            Expense::updateOrCreate(
                [
                    'type' => $row['type'],
                    'teacher_id' => $row['teacher_key'] ? $teachers[$row['teacher_key']]->id : null,
                    'amount' => $row['amount'],
                    'expense_date' => $row['expense_date'],
                    'note' => $row['note'],
                    'created_by' => $users[$row['created_by_key']]->id,
                ],
                []
            );
        }
    }
}
