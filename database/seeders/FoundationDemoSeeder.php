<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\FeeHead;
use App\Models\FeeStructure;
use App\Models\Guardian;
use App\Models\Program;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentEnrollment;
use App\Models\StudentFeeOverride;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\TenantBillingConfig;
use App\Models\Teacher;
use App\Models\TenantSetting;
use App\Models\User;
use App\Services\AcademicCatalogService;
use App\Support\PermissionRegistry;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\PermissionRegistrar;

class FoundationDemoSeeder extends Seeder
{
    public function __construct(
        protected AcademicCatalogService $academicCatalogService,
    ) {
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        PermissionRegistry::syncPermissions();
        PermissionRegistry::syncSuperAdminRole();

        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'owner@coaching-saas.test'],
            [
                'tenant_id' => null,
                'name' => 'Platform Owner',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ],
        );

        $superAdmin->syncRoles([]);
        $permissionRegistrar = app(PermissionRegistrar::class);
        $permissionRegistrar->setPermissionsTeamId(PermissionRegistry::SYSTEM_TEAM_ID);
        $superAdmin->assignRole(Role::SUPER_ADMIN);
        $permissionRegistrar->setPermissionsTeamId(null);

        $tenants = [
            [
                'name' => 'Sunrise Coaching Center',
                'slug' => 'sunrise-coaching-center',
                'status' => Tenant::STATUS_ACTIVE,
                'billing_model' => Tenant::BILLING_MODEL_PER_STUDENT,
                'contact_person' => 'Amina Rahman',
                'phone' => '01700000001',
                'email' => 'admin@sunrise.test',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
            ],
            [
                'name' => 'Scholars Academy',
                'slug' => 'scholars-academy',
                'status' => Tenant::STATUS_TRIAL,
                'billing_model' => Tenant::BILLING_MODEL_PER_BATCH,
                'contact_person' => 'Mahin Islam',
                'phone' => '01700000002',
                'email' => 'admin@scholars.test',
                'city' => 'Chattogram',
                'country' => 'Bangladesh',
            ],
        ];

        foreach ($tenants as $tenantData) {
            $tenant = Tenant::query()->updateOrCreate(
                ['slug' => $tenantData['slug']],
                array_merge($tenantData, [
                    'timezone' => 'Asia/Dhaka',
                    'currency' => 'BDT',
                    'activated_at' => now(),
                    'max_branches' => 1,
                    'max_users' => 25,
                    'max_teachers' => 10,
                    'max_students' => 500,
                ]),
            );

            PermissionRegistry::syncTenantRoles($tenant);
            $this->academicCatalogService->seedDefaults($tenant);
            $this->seedTenantSettings($tenant);
            $this->seedTenantUsers($tenant);
            $this->seedAcademicStructure($tenant);
            $this->seedStudents($tenant);
            $this->seedEnrollments($tenant);
            $this->seedBillingFoundation($tenant);
        }
    }

    protected function seedTenantSettings(Tenant $tenant): void
    {
        $settings = [
            'communication.channels' => [
                'sms' => false,
                'whatsapp' => false,
                'email' => true,
            ],
            'communication.events' => [
                'admission' => true,
                'fee_payment' => true,
                'due_reminder' => false,
                'attendance_alert' => false,
                'exam_notice' => false,
                'result_publish' => false,
            ],
            'billing.defaults' => [
                'model' => $tenant->billing_model,
                'currency' => $tenant->currency,
            ],
        ];

        foreach ($settings as $key => $value) {
            TenantSetting::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->getKey(),
                    'key' => $key,
                ],
                [
                    'value' => $value,
                    'autoload' => true,
                ],
            );
        }
    }

    protected function seedTenantUsers(Tenant $tenant): void
    {
        $password = Hash::make('password');

        $users = match ($tenant->slug) {
            'sunrise-coaching-center' => [
                ['name' => 'Amina Rahman', 'email' => 'admin@sunrise.test', 'role' => Role::ADMIN],
                ['name' => 'Nabil Hasan', 'email' => 'teacher1@sunrise.test', 'role' => Role::TEACHER],
                ['name' => 'Sara Akter', 'email' => 'student1@sunrise.test', 'role' => Role::STUDENT],
                ['name' => 'Farida Begum', 'email' => 'guardian1@sunrise.test', 'role' => Role::GUARDIAN],
            ],
            default => [
                ['name' => 'Mahin Islam', 'email' => 'admin@scholars.test', 'role' => Role::ADMIN],
                ['name' => 'Tariq Hossain', 'email' => 'teacher1@scholars.test', 'role' => Role::TEACHER],
                ['name' => 'Nusrat Jahan', 'email' => 'student1@scholars.test', 'role' => Role::STUDENT],
            ],
        };

        $permissionRegistrar = app(PermissionRegistrar::class);
        $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
        $permissionRegistrar->setPermissionsTeamId($tenant->getKey());

        foreach ($users as $userData) {
            $user = User::query()->updateOrCreate(
                ['email' => $userData['email']],
                [
                    'tenant_id' => $tenant->getKey(),
                    'name' => $userData['name'],
                    'email_verified_at' => now(),
                    'password' => $password,
                ],
            );

            $user->syncRoles([]);
            $user->assignRole($userData['role']);

            if ($userData['role'] === Role::TEACHER) {
                Teacher::query()->updateOrCreate(
                    ['tenant_id' => $tenant->getKey(), 'user_id' => $user->getKey()],
                    [
                        'name' => $userData['name'],
                        'phone' => $tenant->slug === 'sunrise-coaching-center' ? '01710000001' : '01810000001',
                        'email' => $userData['email'],
                        'status' => Teacher::STATUS_ACTIVE,
                        'subject_specializations' => $tenant->slug === 'sunrise-coaching-center'
                            ? ['Mathematics', 'Physics']
                            : ['English', 'ICT'],
                        'address' => $tenant->slug === 'sunrise-coaching-center'
                            ? 'Mirpur, Dhaka'
                            : 'Panchlaish, Chattogram',
                        'bio' => 'Seeded sample teacher profile for module verification.',
                        'can_own_batches' => true,
                        'can_collect_fees' => true,
                        'joined_at' => now()->subMonths(6)->toDateString(),
                    ],
                );
            }
        }

        $permissionRegistrar->setPermissionsTeamId($originalTeamId);
    }

    protected function seedAcademicStructure(Tenant $tenant): void
    {
        $primaryTeacher = Teacher::query()
            ->where('tenant_id', $tenant->getKey())
            ->orderBy('id')
            ->first();

        if (! $primaryTeacher) {
            return;
        }

        $program = Program::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('name', $tenant->slug === 'sunrise-coaching-center' ? 'HSC Science' : 'Class 10')
            ->first();

        $subject = Subject::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('name', $tenant->slug === 'sunrise-coaching-center' ? 'Physics' : 'English')
            ->first();

        $batch = Batch::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->getKey(),
                'code' => $tenant->slug === 'sunrise-coaching-center' ? 'HSC-PHY-A' : 'ENG-10-B',
            ],
            [
                'program_id' => $program?->getKey(),
                'subject_id' => $subject?->getKey(),
                'owner_teacher_id' => $primaryTeacher->getKey(),
                'name' => $tenant->slug === 'sunrise-coaching-center' ? 'HSC Physics Batch A' : 'English Batch B',
                'status' => Batch::STATUS_ACTIVE,
                'capacity' => 40,
                'room_name' => $tenant->slug === 'sunrise-coaching-center' ? 'Room 201' : 'Room 102',
                'starts_on' => now()->startOfMonth()->toDateString(),
                'ends_on' => now()->addMonths(4)->endOfMonth()->toDateString(),
                'notes' => 'Seeded academic batch for module verification.',
            ],
        );

        $batch->schedules()->delete();
        $batch->schedules()->createMany([
            [
                'day_of_week' => 'sunday',
                'start_time' => '09:00',
                'end_time' => '10:30',
                'room_name' => $batch->room_name,
                'sort_order' => 1,
            ],
            [
                'day_of_week' => 'tuesday',
                'start_time' => '09:00',
                'end_time' => '10:30',
                'room_name' => $batch->room_name,
                'sort_order' => 2,
            ],
        ]);
    }

    protected function seedStudents(Tenant $tenant): void
    {
        $teacher = Teacher::query()
            ->where('tenant_id', $tenant->getKey())
            ->orderBy('id')
            ->first();

        if (! $teacher) {
            return;
        }

        $studentUser = User::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('email', $tenant->slug === 'sunrise-coaching-center' ? 'student1@sunrise.test' : 'student1@scholars.test')
            ->first();

        if (! $studentUser) {
            return;
        }

        $student = Student::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->getKey(),
                'student_code' => $tenant->slug === 'sunrise-coaching-center' ? 'STU-1001' : 'STU-2001',
            ],
            [
                'user_id' => $studentUser->getKey(),
                'owner_teacher_id' => $teacher->getKey(),
                'name' => $studentUser->name,
                'phone' => $tenant->slug === 'sunrise-coaching-center' ? '01910000001' : '01920000001',
                'email' => $studentUser->email,
                'admission_date' => now()->subMonths(2)->toDateString(),
                'status' => Student::STATUS_ACTIVE,
                'institution_name' => $tenant->slug === 'sunrise-coaching-center' ? 'Mirpur Girls School' : 'Scholars High School',
                'institution_class' => $tenant->slug === 'sunrise-coaching-center' ? 'HSC Science' : 'Class 10',
                'address' => $tenant->slug === 'sunrise-coaching-center' ? 'Mirpur DOHS, Dhaka' : 'Panchlaish, Chattogram',
                'notes' => 'Seeded student profile for module verification.',
            ],
        );

        $guardianUser = User::query()
            ->where('tenant_id', $tenant->getKey())
            ->where('email', $tenant->slug === 'sunrise-coaching-center' ? 'guardian1@sunrise.test' : 'guardian1@scholars.test')
            ->first();

        $guardian = Guardian::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->getKey(),
                'user_id' => $guardianUser?->getKey(),
                'name' => $tenant->slug === 'sunrise-coaching-center' ? 'Farida Begum' : 'Abdul Karim',
            ],
            [
                'phone' => $tenant->slug === 'sunrise-coaching-center' ? '01810000001' : '01820000001',
                'email' => $guardianUser?->email,
                'occupation' => $tenant->slug === 'sunrise-coaching-center' ? 'School Teacher' : 'Business Owner',
                'address' => $tenant->slug === 'sunrise-coaching-center' ? 'Mirpur DOHS, Dhaka' : 'Nasirabad, Chattogram',
            ],
        );

        $student->guardians()->syncWithoutDetaching([
            $guardian->getKey() => [
                'tenant_id' => $tenant->getKey(),
                'relation_type' => $tenant->slug === 'sunrise-coaching-center' ? Guardian::RELATION_MOTHER : Guardian::RELATION_FATHER,
                'is_primary' => true,
                'notes' => 'Seeded primary guardian.',
            ],
        ]);
    }

    protected function seedEnrollments(Tenant $tenant): void
    {
        $student = Student::query()
            ->where('tenant_id', $tenant->getKey())
            ->orderBy('id')
            ->first();

        $batch = Batch::query()
            ->where('tenant_id', $tenant->getKey())
            ->orderBy('id')
            ->first();

        if (! $student || ! $batch) {
            return;
        }

        StudentEnrollment::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->getKey(),
                'student_id' => $student->getKey(),
                'batch_id' => $batch->getKey(),
            ],
            [
                'enrolled_at' => now()->subMonth()->toDateString(),
                'status' => StudentEnrollment::STATUS_ACTIVE,
                'notes' => 'Seeded enrollment for module verification.',
            ],
        );
    }

    protected function seedBillingFoundation(Tenant $tenant): void
    {
        TenantBillingConfig::query()->updateOrCreate(
            ['tenant_id' => $tenant->getKey()],
            [
                'billing_model' => $tenant->billing_model,
                'config' => match ($tenant->billing_model) {
                    Tenant::BILLING_MODEL_PER_STUDENT => [
                        'billing_period' => 'monthly',
                        'unique_student_per_period' => true,
                        'count_each_batch_separately' => false,
                        'count_each_course_separately' => false,
                        'notes' => 'Seeded per-student tenant config.',
                    ],
                    Tenant::BILLING_MODEL_PER_BATCH => [
                        'billing_period' => 'monthly',
                        'unique_student_per_period' => false,
                        'count_each_batch_separately' => true,
                        'count_each_course_separately' => false,
                        'notes' => 'Seeded per-batch tenant config.',
                    ],
                    default => [
                        'billing_period' => 'monthly',
                        'unique_student_per_period' => false,
                        'count_each_batch_separately' => false,
                        'count_each_course_separately' => true,
                        'notes' => 'Seeded billing config.',
                    ],
                },
            ]
        );

        $admissionFeeHead = FeeHead::query()->updateOrCreate(
            ['tenant_id' => $tenant->getKey(), 'code' => 'ADM'],
            [
                'name' => 'Admission Fee',
                'type' => FeeHead::TYPE_ADMISSION,
                'frequency' => FeeHead::FREQUENCY_ONE_TIME,
                'is_active' => true,
                'description' => 'One-time admission charge.',
            ]
        );

        $tuitionFeeHead = FeeHead::query()->updateOrCreate(
            ['tenant_id' => $tenant->getKey(), 'code' => 'TUI'],
            [
                'name' => 'Monthly Tuition',
                'type' => FeeHead::TYPE_MONTHLY_TUITION,
                'frequency' => FeeHead::FREQUENCY_MONTHLY,
                'is_active' => true,
                'description' => 'Recurring tuition charge.',
            ]
        );

        $examFeeHead = FeeHead::query()->updateOrCreate(
            ['tenant_id' => $tenant->getKey(), 'code' => 'EXM'],
            [
                'name' => 'Exam Fee',
                'type' => FeeHead::TYPE_EXAM,
                'frequency' => FeeHead::FREQUENCY_CUSTOM,
                'is_active' => true,
                'description' => 'Assessment and exam related charge.',
            ]
        );

        $batch = Batch::query()->where('tenant_id', $tenant->getKey())->orderBy('id')->first();
        $program = Program::query()->where('tenant_id', $tenant->getKey())->orderBy('id')->first();
        $student = Student::query()->where('tenant_id', $tenant->getKey())->orderBy('id')->first();

        FeeStructure::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->getKey(),
                'fee_head_id' => $admissionFeeHead->getKey(),
                'title' => 'Default Admission Charge',
            ],
            [
                'billing_model' => null,
                'applicable_type' => FeeStructure::APPLICABLE_TENANT,
                'applicable_id' => null,
                'amount' => $tenant->slug === 'sunrise-coaching-center' ? 1500 : 1200,
                'is_active' => true,
                'starts_on' => now()->startOfYear()->toDateString(),
                'ends_on' => null,
                'notes' => 'Seeded tenant-wide admission structure.',
            ]
        );

        $tuitionStructure = FeeStructure::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->getKey(),
                'fee_head_id' => $tuitionFeeHead->getKey(),
                'title' => $tenant->billing_model === Tenant::BILLING_MODEL_PER_BATCH ? 'Batch Tuition Charge' : 'Monthly Student Tuition',
            ],
            [
                'billing_model' => $tenant->billing_model,
                'applicable_type' => $tenant->billing_model === Tenant::BILLING_MODEL_PER_BATCH ? FeeStructure::APPLICABLE_BATCH : FeeStructure::APPLICABLE_TENANT,
                'applicable_id' => $tenant->billing_model === Tenant::BILLING_MODEL_PER_BATCH ? $batch?->getKey() : null,
                'amount' => $tenant->billing_model === Tenant::BILLING_MODEL_PER_BATCH ? 1800 : 1000,
                'is_active' => true,
                'starts_on' => now()->startOfMonth()->toDateString(),
                'ends_on' => null,
                'notes' => 'Seeded tuition pricing structure.',
            ]
        );

        FeeStructure::query()->updateOrCreate(
            [
                'tenant_id' => $tenant->getKey(),
                'fee_head_id' => $examFeeHead->getKey(),
                'title' => 'Program Exam Charge',
            ],
            [
                'billing_model' => null,
                'applicable_type' => FeeStructure::APPLICABLE_PROGRAM,
                'applicable_id' => $program?->getKey(),
                'amount' => 600,
                'is_active' => true,
                'starts_on' => now()->startOfMonth()->toDateString(),
                'ends_on' => null,
                'notes' => 'Seeded program-level exam structure.',
            ]
        );

        if ($student && $tuitionStructure) {
            StudentFeeOverride::query()->updateOrCreate(
                [
                    'tenant_id' => $tenant->getKey(),
                    'student_id' => $student->getKey(),
                    'fee_structure_id' => $tuitionStructure->getKey(),
                ],
                [
                    'amount' => $tenant->slug === 'sunrise-coaching-center' ? 900 : 1700,
                    'is_active' => true,
                    'starts_on' => now()->startOfMonth()->toDateString(),
                    'ends_on' => now()->addMonths(2)->endOfMonth()->toDateString(),
                    'reason' => 'Seeded override for verification.',
                ]
            );
        }
    }
}
