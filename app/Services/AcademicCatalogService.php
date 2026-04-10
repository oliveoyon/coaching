<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Subject;
use App\Models\Tenant;

class AcademicCatalogService
{
    public function seedDefaults(Tenant $tenant): void
    {
        $programs = [
            ['name' => 'Class 9', 'code' => 'CLS-9'],
            ['name' => 'Class 10', 'code' => 'CLS-10'],
            ['name' => 'HSC Science', 'code' => 'HSC-SCI'],
            ['name' => 'Admission', 'code' => 'ADMISSION'],
        ];

        foreach ($programs as $program) {
            Program::query()->updateOrCreate(
                ['tenant_id' => $tenant->getKey(), 'name' => $program['name']],
                [
                    'code' => $program['code'],
                    'status' => Program::STATUS_ACTIVE,
                ],
            );
        }

        $subjects = [
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Physics', 'code' => 'PHY'],
            ['name' => 'Chemistry', 'code' => 'CHEM'],
            ['name' => 'Biology', 'code' => 'BIO'],
            ['name' => 'English', 'code' => 'ENG'],
            ['name' => 'ICT', 'code' => 'ICT'],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->updateOrCreate(
                ['tenant_id' => $tenant->getKey(), 'name' => $subject['name']],
                [
                    'code' => $subject['code'],
                    'status' => Subject::STATUS_ACTIVE,
                ],
            );
        }
    }
}
