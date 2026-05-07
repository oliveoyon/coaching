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

class ImportWorkbookSeeder extends Seeder
{
    protected string $importPath;

    /**
     * Seed the application's database from import CSV files.
     */
    public function run(): void
    {
        $this->importPath = storage_path('app/imports');

        $this->command?->info('Starting workbook import from: '.$this->importPath);

        $this->importClasses();
        $this->importSubjects();
        $this->importUsers();
        $this->importTeachers();
        $this->importBatches();
        $this->importBatchSchedules();
        $this->importBatchTeachers();
        $this->importFeeTypes();
        $this->importBatchFees();
        $this->importStudents();
        $this->importEnrollments();
        $this->importPayments();
        $this->importTeacherSettlements();
        $this->importExpenses();
    }

    protected function importClasses(): void
    {
        $this->rows('classes.csv')->each(function (array $row): void {
            AcademicClass::updateOrCreate(
                ['name' => $this->value($row, 'name')],
                ['status' => $this->value($row, 'status', 'active')]
            );
        });
    }

    protected function importSubjects(): void
    {
        $this->rows('subjects.csv')->each(function (array $row): void {
            Subject::updateOrCreate(
                ['name' => $this->value($row, 'name')],
                ['status' => $this->value($row, 'status', 'active')]
            );
        });
    }

    protected function importUsers(): void
    {
        $this->rows('users.csv')->each(function (array $row): void {
            $email = Str::lower($this->value($row, 'email'));
            $password = $this->value($row, 'password', 'password');
            $roleName = $this->value($row, 'role');

            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $this->value($row, 'name'),
                    'username' => $this->value($row, 'username'),
                    'password' => Hash::make($password),
                    'status' => $this->value($row, 'status', 'active'),
                    'email_verified_at' => now(),
                ]
            );

            if ($roleName !== '') {
                $role = Role::query()->where('name', $roleName)->first();

                if (! $role) {
                    $this->command?->warn("Skipped role assignment for {$email}. Role not found: {$roleName}");
                } else {
                    $user->syncRoles([$roleName]);
                }
            }
        });
    }

    protected function importTeachers(): void
    {
        $this->rows('teachers.csv')->each(function (array $row): void {
            $email = Str::lower($this->value($row, 'user_email'));
            $user = User::query()->where('email', $email)->first();

            if (! $user) {
                $this->command?->warn("Skipped teacher row. User not found: {$email}");
                return;
            }

            $user->syncRoles(['Teacher']);

            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                ['status' => $this->value($row, 'status', 'active')]
            );
        });
    }

    protected function importBatches(): void
    {
        $this->rows('batches.csv')->each(function (array $row): void {
            $class = $this->findClass($this->value($row, 'class_name'));
            $subjectName = $this->value($row, 'subject_name');
            $subject = $subjectName !== '' ? $this->findSubject($subjectName) : null;

            if (! $class) {
                $this->command?->warn('Skipped batch. Class not found: '.$this->value($row, 'class_name'));
                return;
            }

            Batch::updateOrCreate(
                [
                    'name' => $this->value($row, 'name'),
                    'class_id' => $class->id,
                    'subject_id' => $subject?->id,
                ],
                [
                    'monthly_fee' => (float) $this->value($row, 'monthly_fee', '0'),
                    'distribution_type' => $this->value($row, 'distribution_type', 'single'),
                    'status' => $this->value($row, 'status', 'active'),
                ]
            );
        });
    }

    protected function importBatchSchedules(): void
    {
        $grouped = $this->rows('batch_schedules.csv')->groupBy(fn (array $row) => $this->value($row, 'batch_name'));

        $grouped->each(function (Collection $rows, string $batchName): void {
            $batch = Batch::query()->where('name', $batchName)->first();

            if (! $batch) {
                $this->command?->warn("Skipped schedule rows. Batch not found: {$batchName}");
                return;
            }

            $slots = $rows
                ->map(fn (array $row) => [
                    'day' => $this->value($row, 'day'),
                    'start_time' => $this->value($row, 'start_time'),
                    'end_time' => $this->value($row, 'end_time'),
                ])
                ->filter(fn (array $slot) => $slot['day'] !== '' && $slot['start_time'] !== '' && $slot['end_time'] !== '')
                ->values()
                ->all();

            $batch->update([
                'schedule_slots' => $slots === [] ? null : $slots,
                'schedule_days' => $slots === [] ? null : collect($slots)->pluck('day')->unique()->values()->all(),
                'start_time' => $slots[0]['start_time'] ?? null,
                'end_time' => $slots[0]['end_time'] ?? null,
            ]);
        });
    }

    protected function importBatchTeachers(): void
    {
        $grouped = $this->rows('batch_teachers.csv')->groupBy(fn (array $row) => $this->value($row, 'batch_name'));

        $grouped->each(function (Collection $rows, string $batchName): void {
            $batch = Batch::query()->where('name', $batchName)->first();

            if (! $batch) {
                $this->command?->warn("Skipped batch teachers. Batch not found: {$batchName}");
                return;
            }

            $teacherIds = $rows
                ->map(function (array $row) {
                    $teacher = $this->findTeacherByEmail($this->value($row, 'teacher_email'));
                    return $teacher?->id;
                })
                ->filter()
                ->unique()
                ->values()
                ->all();

            if ($teacherIds !== []) {
                $batch->teachers()->sync($teacherIds);
            }
        });
    }

    protected function importFeeTypes(): void
    {
        $this->rows('fee_types.csv')->each(function (array $row): void {
            FeeType::updateOrCreate(
                ['code' => $this->value($row, 'code')],
                [
                    'name' => $this->value($row, 'name'),
                    'frequency' => $this->value($row, 'frequency', 'manual'),
                    'status' => $this->value($row, 'status', 'active'),
                ]
            );
        });
    }

    protected function importBatchFees(): void
    {
        $this->rows('batch_fees.csv')->each(function (array $row): void {
            $batch = Batch::query()->where('name', $this->value($row, 'batch_name'))->first();
            $feeType = $this->findFeeType($this->value($row, 'fee_type_code'), $this->value($row, 'fee_type_name'));

            if (! $batch || ! $feeType) {
                $this->command?->warn('Skipped batch fee row for batch: '.$this->value($row, 'batch_name'));
                return;
            }

            BatchFee::updateOrCreate(
                [
                    'batch_id' => $batch->id,
                    'fee_type_id' => $feeType->id,
                ],
                [
                    'amount' => (float) $this->value($row, 'amount', '0'),
                    'status' => $this->value($row, 'status', 'active'),
                ]
            );
        });
    }

    protected function importStudents(): void
    {
        $this->rows('students.csv')->each(function (array $row): void {
            $class = $this->findClass($this->value($row, 'class_name'));

            if (! $class) {
                $this->command?->warn('Skipped student. Class not found: '.$this->value($row, 'class_name'));
                return;
            }

            $lookup = $this->value($row, 'student_code') !== ''
                ? ['student_code' => $this->value($row, 'student_code')]
                : ['name' => $this->value($row, 'name'), 'guardian_phone' => $this->value($row, 'guardian_phone')];

            Student::updateOrCreate(
                $lookup,
                [
                    'name' => $this->value($row, 'name'),
                    'class_id' => $class->id,
                    'phone' => $this->nullable($row, 'phone'),
                    'guardian_phone' => $this->nullable($row, 'guardian_phone'),
                    'school' => $this->nullable($row, 'school'),
                    'address' => $this->nullable($row, 'address'),
                    'photo_path' => $this->nullable($row, 'photo_path'),
                    'status' => $this->value($row, 'status', 'active'),
                ]
            );
        });
    }

    protected function importEnrollments(): void
    {
        $this->rows('enrollments.csv')->each(function (array $row): void {
            $student = Student::query()->where('student_code', $this->value($row, 'student_code'))->first();
            $batch = Batch::query()->where('name', $this->value($row, 'batch_name'))->first();
            $creator = User::query()->where('email', Str::lower($this->value($row, 'created_by_email')))->first();

            if (! $student || ! $batch) {
                $this->command?->warn('Skipped enrollment row for student: '.$this->value($row, 'student_code'));
                return;
            }

            Enrollment::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'batch_id' => $batch->id,
                    'start_date' => $this->value($row, 'start_date'),
                ],
                [
                    'end_date' => $this->nullable($row, 'end_date'),
                    'status' => $this->value($row, 'status', 'active'),
                    'created_by' => $creator?->id,
                ]
            );
        });
    }

    protected function importPayments(): void
    {
        $distributionService = app(IncomeDistributionService::class);

        $this->rows('payments.csv')->each(function (array $row) use ($distributionService): void {
            $student = Student::query()->where('student_code', $this->value($row, 'student_code'))->first();
            $batch = Batch::query()->where('name', $this->value($row, 'batch_name'))->first();
            $feeType = $this->findFeeType($this->value($row, 'fee_type_code'), $this->value($row, 'fee_type_name'));
            $collector = User::query()->where('email', Str::lower($this->value($row, 'collected_by_email')))->first();
            $approver = User::query()->where('email', Str::lower($this->value($row, 'approved_by_email')))->first();

            if (! $student || ! $batch || ! $feeType) {
                $this->command?->warn('Skipped payment row for student: '.$this->value($row, 'student_code'));
                return;
            }

            $enrollment = Enrollment::query()
                ->where('student_id', $student->id)
                ->where('batch_id', $batch->id)
                ->orderByDesc('start_date')
                ->first();

            $batchFee = BatchFee::query()
                ->where('batch_id', $batch->id)
                ->where('fee_type_id', $feeType->id)
                ->first();

            if (! $enrollment || ! $batchFee) {
                $this->command?->warn('Skipped payment row. Enrollment or batch fee not found for student: '.$this->value($row, 'student_code'));
                return;
            }

            $payment = Payment::updateOrCreate(
                [
                    'enrollment_id' => $enrollment->id,
                    'batch_fee_id' => $batchFee->id,
                    'month' => $this->nullable($row, 'month'),
                    'payment_date' => $this->value($row, 'payment_date'),
                    'method' => $this->value($row, 'method', 'cash'),
                    'transaction_id' => $this->nullable($row, 'transaction_id'),
                ],
                [
                    'amount' => (float) $this->value($row, 'amount', '0'),
                    'status' => $this->value($row, 'status', 'approved'),
                    'collected_by' => $collector?->id,
                    'approved_by' => $approver?->id,
                ]
            );

            if ($payment->status === 'approved') {
                $distributionService->distribute($payment);
            }
        });
    }

    protected function importTeacherSettlements(): void
    {
        $settlementService = app(TeacherSettlementService::class);

        $this->rows('teacher_settlements.csv')->each(function (array $row) use ($settlementService): void {
            $teacher = $this->findTeacherByEmail($this->value($row, 'teacher_email'));
            $payer = User::query()->where('email', Str::lower($this->value($row, 'paid_by_email')))->first();

            if (! $teacher || ! $payer) {
                $this->command?->warn('Skipped settlement row for teacher: '.$this->value($row, 'teacher_email'));
                return;
            }

            $alreadyExists = TeacherSettlement::query()
                ->where('teacher_id', $teacher->id)
                ->where('amount', (float) $this->value($row, 'amount', '0'))
                ->whereDate('settlement_date', $this->value($row, 'settlement_date'))
                ->where('paid_by', $payer->id)
                ->where('note', $this->nullable($row, 'note'))
                ->exists();

            if ($alreadyExists) {
                return;
            }

            $settlementService->settle(
                $teacher,
                (float) $this->value($row, 'amount', '0'),
                $this->value($row, 'settlement_date'),
                $payer->id,
                $this->nullable($row, 'note'),
            );
        });
    }

    protected function importExpenses(): void
    {
        $this->rows('expenses.csv')->each(function (array $row): void {
            $teacher = $this->value($row, 'teacher_email') !== '' ? $this->findTeacherByEmail($this->value($row, 'teacher_email')) : null;
            $creator = User::query()->where('email', Str::lower($this->value($row, 'created_by_email')))->first();

            if (! $creator) {
                $this->command?->warn('Skipped expense row. Creator not found: '.$this->value($row, 'created_by_email'));
                return;
            }

            Expense::updateOrCreate(
                [
                    'type' => $this->value($row, 'type', 'common'),
                    'teacher_id' => $teacher?->id,
                    'amount' => (float) $this->value($row, 'amount', '0'),
                    'expense_date' => $this->value($row, 'expense_date'),
                    'note' => $this->nullable($row, 'note'),
                    'created_by' => $creator->id,
                ],
                []
            );
        });
    }

    /**
     * Read a CSV file from storage/app/imports.
     *
     * @return \Illuminate\Support\Collection<int, array<string, string>>
     */
    protected function rows(string $fileName): Collection
    {
        $path = $this->importPath.DIRECTORY_SEPARATOR.$fileName;

        if (! is_file($path)) {
            $this->command?->warn("Import file not found, skipped: {$fileName}");
            return collect();
        }

        $handle = fopen($path, 'r');

        if ($handle === false) {
            $this->command?->warn("Unable to read import file: {$fileName}");
            return collect();
        }

        $headers = fgetcsv($handle) ?: [];
        $headers = array_map(fn ($header) => Str::of((string) $header)->trim()->lower()->toString(), $headers);

        $rows = collect();

        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $rows->push(array_combine($headers, array_map(fn ($value) => trim((string) $value), $row)));
        }

        fclose($handle);

        return $rows;
    }

    protected function findClass(string $name): ?AcademicClass
    {
        return AcademicClass::query()->where('name', $name)->first();
    }

    protected function findSubject(string $name): ?Subject
    {
        return Subject::query()->where('name', $name)->first();
    }

    protected function findTeacherByEmail(string $email): ?Teacher
    {
        return Teacher::query()->whereHas('user', fn ($query) => $query->where('email', Str::lower($email)))->first();
    }

    protected function findFeeType(string $code, string $name): ?FeeType
    {
        return FeeType::query()
            ->when($code !== '', fn ($query) => $query->where('code', $code))
            ->when($code === '' && $name !== '', fn ($query) => $query->where('name', $name))
            ->first();
    }

    protected function value(array $row, string $key, string $default = ''): string
    {
        return trim((string) ($row[$key] ?? $default));
    }

    protected function nullable(array $row, string $key): ?string
    {
        $value = $this->value($row, $key);

        return $value === '' ? null : $value;
    }
}
