<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;

$users = User::query()
    ->with('roles')
    ->orderBy('id')
    ->get()
    ->map(function (User $user) {
        return [
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'password' => 'password',
            'status' => $user->status,
            'role' => $user->getRoleNames()->first(),
        ];
    })
    ->values()
    ->all();

$teachers = Teacher::query()
    ->with('user')
    ->orderBy('id')
    ->get()
    ->map(function (Teacher $teacher) {
        return [
            'user_email' => $teacher->user?->email,
            'status' => $teacher->status,
        ];
    })
    ->values()
    ->all();

$classes = AcademicClass::query()
    ->orderBy('id')
    ->get(['name', 'status'])
    ->map(fn ($class) => $class->toArray())
    ->values()
    ->all();

$subjects = Subject::query()
    ->orderBy('id')
    ->get(['name', 'status'])
    ->map(fn ($subject) => $subject->toArray())
    ->values()
    ->all();

$batches = Batch::query()
    ->with(['academicClass', 'subject', 'teachers.user'])
    ->orderBy('id')
    ->get()
    ->map(function (Batch $batch) {
        return [
            'name' => $batch->name,
            'class_name' => $batch->academicClass?->name,
            'subject_name' => $batch->subject?->name,
            'monthly_fee' => (float) $batch->monthly_fee,
            'distribution_type' => $batch->distribution_type,
            'status' => $batch->status,
            'schedule_slots' => $batch->schedule_entries,
            'teacher_emails' => $batch->teachers
                ->pluck('user.email')
                ->filter()
                ->values()
                ->all(),
        ];
    })
    ->values()
    ->all();

$export = var_export(compact('users', 'teachers', 'classes', 'subjects', 'batches'), true);

$stub = <<<PHP
<?php

namespace Database\\Seeders;

use App\\Models\\AcademicClass;
use App\\Models\\Batch;
use App\\Models\\Subject;
use App\\Models\\Teacher;
use App\\Models\\User;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\Hash;
use Spatie\\Permission\\Models\\Role;

class MasterDataBackupSeeder extends Seeder
{
    /**
     * Seed the preserved master data.
     */
    public function run(): void
    {
        \$data = {$export};

        foreach (\$data['users'] as \$row) {
            \$user = User::updateOrCreate(
                ['email' => \$row['email']],
                [
                    'name' => \$row['name'],
                    'username' => \$row['username'],
                    'password' => Hash::make(\$row['password']),
                    'status' => \$row['status'],
                    'email_verified_at' => now(),
                ],
            );

            if (! empty(\$row['role'])) {
                \$role = Role::query()->where('name', \$row['role'])->first();

                if (\$role) {
                    \$user->syncRoles([\$role->name]);
                }
            }
        }

        foreach (\$data['teachers'] as \$row) {
            \$user = User::query()->where('email', \$row['user_email'])->first();

            if (! \$user) {
                continue;
            }

            Teacher::updateOrCreate(
                ['user_id' => \$user->id],
                ['status' => \$row['status']],
            );
        }

        foreach (\$data['classes'] as \$row) {
            AcademicClass::updateOrCreate(
                ['name' => \$row['name']],
                ['status' => \$row['status']],
            );
        }

        foreach (\$data['subjects'] as \$row) {
            Subject::updateOrCreate(
                ['name' => \$row['name']],
                ['status' => \$row['status']],
            );
        }

        foreach (\$data['batches'] as \$row) {
            \$class = AcademicClass::query()->where('name', \$row['class_name'])->first();
            \$subject = ! empty(\$row['subject_name'])
                ? Subject::query()->where('name', \$row['subject_name'])->first()
                : null;

            if (! \$class) {
                continue;
            }

            \$batch = Batch::updateOrCreate(
                ['name' => \$row['name']],
                [
                    'class_id' => \$class->id,
                    'subject_id' => \$subject?->id,
                    'monthly_fee' => \$row['monthly_fee'],
                    'distribution_type' => \$row['distribution_type'],
                    'schedule_slots' => \$row['schedule_slots'],
                    'status' => \$row['status'],
                ],
            );

            \$teacherIds = Teacher::query()
                ->whereHas('user', fn (\$query) => \$query->whereIn('email', \$row['teacher_emails'] ?? []))
                ->pluck('id')
                ->all();

            \$batch->teachers()->sync(\$teacherIds);
        }
    }
}
PHP;

file_put_contents(__DIR__ . '/../database/seeders/MasterDataBackupSeeder.php', $stub);

echo "users=" . count($users) . PHP_EOL;
echo "teachers=" . count($teachers) . PHP_EOL;
echo "classes=" . count($classes) . PHP_EOL;
echo "subjects=" . count($subjects) . PHP_EOL;
echo "batches=" . count($batches) . PHP_EOL;
echo "written=database/seeders/MasterDataBackupSeeder.php" . PHP_EOL;
