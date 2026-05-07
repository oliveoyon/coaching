<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class CoachingBatchWorkbookSeeder extends Seeder
{
    /**
     * Batches created from:
     * C:\Users\Arif\Downloads\coaching batch.xlsx
     *
     * Teacher mapping used:
     * 1 = Masum Bin Wohab
     * 2 = Sanowar Hossain
     * 3 = Abu Salek
     */
    public function run(): void
    {
        $rows = [
            ['name' => 'IXDRMC1', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => [['day' => 'fri', 'start_time' => '17:00', 'end_time' => '18:00'], ['day' => 'sat', 'start_time' => '17:00', 'end_time' => '18:00']]],
            ['name' => 'IXDRMC2', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => [['day' => 'fri', 'start_time' => '18:00', 'end_time' => '19:00'], ['day' => 'sat', 'start_time' => '18:00', 'end_time' => '19:00']]],
            ['name' => 'IXDRMC3', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => [['day' => 'tue', 'start_time' => '18:00', 'end_time' => '19:00'], ['day' => 'thu', 'start_time' => '18:00', 'end_time' => '19:00']]],
            ['name' => 'IXDRMC4', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => [['day' => 'fri', 'start_time' => '10:00', 'end_time' => '11:00'], ['day' => 'sat', 'start_time' => '10:00', 'end_time' => '11:00']]],
            ['name' => 'IXDRMCV', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => [['day' => 'mon', 'start_time' => '18:00', 'end_time' => '19:00'], ['day' => 'wed', 'start_time' => '18:00', 'end_time' => '19:00']]],
            ['name' => 'XDRMC1', 'class_name' => 'Class 10 - X', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => [['day' => 'fri', 'start_time' => '11:00', 'end_time' => '12:00'], ['day' => 'sat', 'start_time' => '11:00', 'end_time' => '12:00']]],
            ['name' => 'XDRMC2', 'class_name' => 'Class 10 - X', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => []],
            ['name' => 'XDRMC3', 'class_name' => 'Class 10 - X', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => [['day' => 'sun', 'start_time' => '16:00', 'end_time' => '17:00'], ['day' => 'tue', 'start_time' => '16:00', 'end_time' => '17:00']]],
            ['name' => 'XIDRMC1', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => [['day' => 'fri', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'sat', 'start_time' => '08:00', 'end_time' => '09:00']]],
            ['name' => 'XIDRMC2', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => []],
            ['name' => 'XDRMC1-26', 'class_name' => 'Class 10 - X', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => []],
            ['name' => 'XDRMC2-26', 'class_name' => 'Class 10 - X', 'subject_name' => 'Chemistry', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [1], 'schedule_slots' => []],
            ['name' => 'IXA', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'mon', 'start_time' => '18:00', 'end_time' => '19:00'], ['day' => 'wed', 'start_time' => '18:00', 'end_time' => '19:00']]],
            ['name' => 'IXB', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'fri', 'start_time' => '16:00', 'end_time' => '17:00'], ['day' => 'sat', 'start_time' => '16:00', 'end_time' => '17:00']]],
            ['name' => 'IXC', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'mon', 'start_time' => '17:00', 'end_time' => '18:00'], ['day' => 'wed', 'start_time' => '17:00', 'end_time' => '18:00']]],
            ['name' => 'IXD', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'fri', 'start_time' => '19:00', 'end_time' => '20:00'], ['day' => 'sat', 'start_time' => '19:00', 'end_time' => '20:00']]],
            ['name' => 'IXE', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'fri', 'start_time' => '11:00', 'end_time' => '12:00'], ['day' => 'sat', 'start_time' => '11:00', 'end_time' => '12:00']]],
            ['name' => 'IXF', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'sun', 'start_time' => '16:00', 'end_time' => '17:00'], ['day' => 'tue', 'start_time' => '16:00', 'end_time' => '17:00']]],
            ['name' => 'IXV', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'sun', 'start_time' => '18:00', 'end_time' => '19:00'], ['day' => 'thu', 'start_time' => '18:00', 'end_time' => '19:00']]],
            ['name' => 'XA', 'class_name' => 'Class 10 - X', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'sun', 'start_time' => '17:00', 'end_time' => '18:00'], ['day' => 'tue', 'start_time' => '17:00', 'end_time' => '18:00']]],
            ['name' => 'XB', 'class_name' => 'Class 10 - X', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'fri', 'start_time' => '10:00', 'end_time' => '11:00'], ['day' => 'sat', 'start_time' => '10:00', 'end_time' => '11:00']]],
            ['name' => 'XC', 'class_name' => 'Class 10 - X', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => []],
            ['name' => 'XD', 'class_name' => 'Class 10 - X', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => []],
            ['name' => 'XIA', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => [['day' => 'fri', 'start_time' => '15:00', 'end_time' => '16:00'], ['day' => 'sat', 'start_time' => '15:00', 'end_time' => '16:00']]],
            ['name' => 'XIB', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => []],
            ['name' => 'XIA-26', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => []],
            ['name' => 'XIB-26', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Physics', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [2], 'schedule_slots' => []],
            ['name' => 'IXB1', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'fri', 'start_time' => '18:00', 'end_time' => '19:00'], ['day' => 'sat', 'start_time' => '18:00', 'end_time' => '19:00']]],
            ['name' => 'IXB2', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'fri', 'start_time' => '17:00', 'end_time' => '18:00'], ['day' => 'sat', 'start_time' => '17:00', 'end_time' => '18:00']]],
            ['name' => 'IXB3', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'tue', 'start_time' => '17:00', 'end_time' => '18:00'], ['day' => 'thu', 'start_time' => '17:00', 'end_time' => '18:00']]],
            ['name' => 'IXB4', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'mon', 'start_time' => '17:00', 'end_time' => '18:00'], ['day' => 'wed', 'start_time' => '17:00', 'end_time' => '18:00']]],
            ['name' => 'IXB5', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'mon', 'start_time' => '15:00', 'end_time' => '16:00'], ['day' => 'wed', 'start_time' => '15:00', 'end_time' => '16:00']]],
            ['name' => 'IXB6', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'fri', 'start_time' => '09:00', 'end_time' => '10:00'], ['day' => 'sat', 'start_time' => '09:00', 'end_time' => '10:00']]],
            ['name' => 'IXBV1', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'fri', 'start_time' => '10:00', 'end_time' => '11:00'], ['day' => 'sat', 'start_time' => '10:00', 'end_time' => '11:00']]],
            ['name' => 'IXBV2', 'class_name' => 'Class 9 - IX', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'fri', 'start_time' => '16:00', 'end_time' => '17:00'], ['day' => 'sat', 'start_time' => '16:00', 'end_time' => '17:00']]],
            ['name' => 'XB1', 'class_name' => 'Class 10 - X', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'fri', 'start_time' => '11:00', 'end_time' => '12:00'], ['day' => 'sat', 'start_time' => '11:00', 'end_time' => '12:00']]],
            ['name' => 'XB2', 'class_name' => 'Class 10 - X', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'fri', 'start_time' => '17:00', 'end_time' => '18:00'], ['day' => 'sat', 'start_time' => '17:00', 'end_time' => '18:00']]],
            ['name' => 'XBV', 'class_name' => 'Class 10 - X', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'sun', 'start_time' => '17:00', 'end_time' => '18:00'], ['day' => 'tue', 'start_time' => '17:00', 'end_time' => '18:00']]],
            ['name' => 'XIB1', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'sun', 'start_time' => '16:00', 'end_time' => '17:00'], ['day' => 'tue', 'start_time' => '16:00', 'end_time' => '17:00']]],
            ['name' => 'XIB2', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'tue', 'start_time' => '19:00', 'end_time' => '20:00'], ['day' => 'thu', 'start_time' => '18:00', 'end_time' => '19:00']]],
            ['name' => 'XIB4', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'wed', 'start_time' => '18:00', 'end_time' => '19:00'], ['day' => 'thu', 'start_time' => '16:00', 'end_time' => '17:00']]],
            ['name' => 'XIB1-26', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'fri', 'start_time' => '08:00', 'end_time' => '09:00'], ['day' => 'sat', 'start_time' => '08:00', 'end_time' => '09:00']]],
            ['name' => 'XIB2-26', 'class_name' => 'Class 11 - XI', 'subject_name' => 'Biology', 'monthly_fee' => 1500, 'distribution_type' => 'single', 'status' => 'active', 'teacher_ids' => [3], 'schedule_slots' => [['day' => 'sun', 'start_time' => '18:00', 'end_time' => '19:00'], ['day' => 'tue', 'start_time' => '18:00', 'end_time' => '19:00']]],
            ['name' => 'VIIIB1', 'class_name' => 'Class 8 - VIII', 'subject_name' => 'Science', 'monthly_fee' => 1500, 'distribution_type' => 'equal', 'status' => 'active', 'teacher_ids' => [1, 2, 3], 'schedule_slots' => [['day' => 'mon', 'start_time' => '16:00', 'end_time' => '17:00'], ['day' => 'wed', 'start_time' => '15:00', 'end_time' => '17:00']]],
            ['name' => 'VIIIB2', 'class_name' => 'Class 8 - VIII', 'subject_name' => 'Science', 'monthly_fee' => 1500, 'distribution_type' => 'equal', 'status' => 'active', 'teacher_ids' => [1, 2, 3], 'schedule_slots' => [['day' => 'sat', 'start_time' => '16:00', 'end_time' => '18:00'], ['day' => 'mon', 'start_time' => '18:00', 'end_time' => '19:00']]],
        ];

        foreach ($rows as $row) {
            $class = AcademicClass::query()->where('name', $row['class_name'])->firstOrFail();
            $subject = Subject::query()->where('name', $row['subject_name'])->firstOrFail();

            $teacherIds = collect($row['teacher_ids'])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $matchedTeacherCount = Teacher::query()
                ->whereIn('id', $teacherIds)
                ->count();

            if ($matchedTeacherCount !== $teacherIds->count()) {
                $this->command?->warn('Skipped batch "'.$row['name'].'" because one or more teacher IDs were not found.');
                continue;
            }

            if ($row['distribution_type'] === 'single' && $teacherIds->count() !== 1) {
                $this->command?->warn('Skipped batch "'.$row['name'].'" because single distribution needs exactly one teacher.');
                continue;
            }

            if ($row['distribution_type'] === 'equal' && $teacherIds->isEmpty()) {
                $this->command?->warn('Skipped batch "'.$row['name'].'" because equal distribution needs at least one teacher.');
                continue;
            }

            $slots = collect($row['schedule_slots'] ?? [])
                ->filter(fn ($slot) => filled($slot['day'] ?? null) && filled($slot['start_time'] ?? null) && filled($slot['end_time'] ?? null))
                ->values();

            $batch = Batch::updateOrCreate(
                [
                    'name' => $row['name'],
                    'class_id' => $class->id,
                    'subject_id' => $subject->id,
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
