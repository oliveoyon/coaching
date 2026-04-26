<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Subject;
use Illuminate\Database\Seeder;

class AcademicSetupSeeder extends Seeder
{
    /**
     * Seed starter classes and subjects.
     */
    public function run(): void
    {
        $classes = [
            'Class 6',
            'Class 7',
            'Class 8',
            'Class 9',
            'Class 10',
            'Class 11',
            'Class 12',
        ];

        $subjects = [
            'Bangla',
            'English',
            'Mathematics',
            'Physics',
            'Chemistry',
            'Biology',
            'ICT',
        ];

        foreach ($classes as $class) {
            AcademicClass::firstOrCreate(['name' => $class], ['status' => 'active']);
        }

        foreach ($subjects as $subject) {
            Subject::firstOrCreate(['name' => $subject], ['status' => 'active']);
        }
    }
}
