<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class BatchBulkSeeder extends Seeder
{
    /**
     * Batch-only bulk seeder.
     *
     * Edit only the $rows array below.
     *
     * Fields match /admin/batches/create:
     * - name
     * - class_name
     * - subject_name (nullable for general batch)
     * - monthly_fee
     * - distribution_type => single | equal
     * - status => active | inactive
     * - teacher_ids => existing teacher IDs
     * - schedule_slots => multiple day/time rows
     */
    public function run(): void
    {
        $rows = [
            [
                'name' => 'Class 8 Batch A',
                'class_name' => 'Class 8 - VIII',
                'subject_name' => null,
                'monthly_fee' => 2200,
                'distribution_type' => 'equal',
                'status' => 'active',
                'teacher_ids' => [1, 2, 3],
                'schedule_slots' => [
                    ['day' => 'sun', 'start_time' => '16:00', 'end_time' => '17:30'],
                    ['day' => 'tue', 'start_time' => '17:00', 'end_time' => '18:30'],
                    ['day' => 'thu', 'start_time' => '16:00', 'end_time' => '17:30'],
                ],
            ],
            [
                'name' => 'Physics Morning',
                'class_name' => 'Class 10 - X',
                'subject_name' => 'Physics',
                'monthly_fee' => 1500,
                'distribution_type' => 'single',
                'status' => 'active',
                'teacher_ids' => [1],
                'schedule_slots' => [
                    ['day' => 'sat', 'start_time' => '08:00', 'end_time' => '09:30'],
                    ['day' => 'mon', 'start_time' => '08:00', 'end_time' => '09:30'],
                ],
            ],
        ];

        foreach ($rows as $row) {
            $class = AcademicClass::query()
                ->where('name', $row['class_name'])
                ->firstOrFail();

            $subject = null;

            if (! empty($row['subject_name'])) {
                $subject = Subject::query()
                    ->where('name', $row['subject_name'])
                    ->firstOrFail();
            }

            $teacherIds = collect($row['teacher_ids'] ?? [])
                ->filter()
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $slots = collect($row['schedule_slots'] ?? [])
                ->map(fn ($slot) => [
                    'day' => (string) ($slot['day'] ?? ''),
                    'start_time' => (string) ($slot['start_time'] ?? ''),
                    'end_time' => (string) ($slot['end_time'] ?? ''),
                ])
                ->filter(fn ($slot) => $slot['day'] !== '' && $slot['start_time'] !== '' && $slot['end_time'] !== '')
                ->values();

            $matchedTeacherCount = Teacher::query()
                ->whereIn('id', $teacherIds)
                ->count();

            if ($matchedTeacherCount !== $teacherIds->count()) {
                $this->command?->warn('Skipped batch "'.$row['name'].'" because one or more teacher IDs were not found.');
                continue;
            }

            if (($row['distribution_type'] ?? 'single') === 'single' && $teacherIds->count() !== 1) {
                $this->command?->warn('Skipped batch "'.$row['name'].'" because single distribution needs exactly one teacher.');
                continue;
            }

            if (($row['distribution_type'] ?? 'single') === 'equal' && $teacherIds->isEmpty()) {
                $this->command?->warn('Skipped batch "'.$row['name'].'" because equal distribution needs at least one teacher.');
                continue;
            }

            $batch = Batch::updateOrCreate(
                [
                    'name' => $row['name'],
                    'class_id' => $class->id,
                    'subject_id' => $subject?->id,
                ],
                [
                    'monthly_fee' => (float) $row['monthly_fee'],
                    'distribution_type' => $row['distribution_type'],
                    'status' => $row['status'],
                    'schedule_slots' => $slots->all(),
                    'schedule_days' => $slots->pluck('day')->unique()->values()->all(),
                    'start_time' => $slots->first()['start_time'] ?? null,
                    'end_time' => $slots->first()['end_time'] ?? null,
                ]
            );

            $batch->teachers()->sync($teacherIds->all());
        }
    }
}
