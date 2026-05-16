<?php

namespace Database\Seeders;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class MasterDataBackupSeeder extends Seeder
{
    /**
     * Seed the preserved master data.
     */
    public function run(): void
    {
        $data = array (
  'users' => 
  array (
    0 => 
    array (
      'name' => 'Super Admin',
      'username' => 'superadmin',
      'email' => 'superadmin@example.com',
      'password' => 'password',
      'status' => 'active',
      'role' => 'Super Admin',
    ),
    1 => 
    array (
      'name' => 'Masum Bin Wohab',
      'username' => 'mmc',
      'email' => 'masum@email.com',
      'password' => 'password',
      'status' => 'active',
      'role' => 'Teacher',
    ),
    2 => 
    array (
      'name' => 'Sanowar Hossain',
      'username' => 'shp',
      'email' => 'sanowar@email.com',
      'password' => 'password',
      'status' => 'active',
      'role' => 'Teacher',
    ),
    3 => 
    array (
      'name' => 'Abu Salek',
      'username' => 'asb',
      'email' => 'salek@email.com',
      'password' => 'password',
      'status' => 'active',
      'role' => 'Teacher',
    ),
  ),
  'teachers' => 
  array (
    0 => 
    array (
      'user_email' => 'masum@email.com',
      'status' => 'active',
    ),
    1 => 
    array (
      'user_email' => 'sanowar@email.com',
      'status' => 'active',
    ),
    2 => 
    array (
      'user_email' => 'salek@email.com',
      'status' => 'active',
    ),
  ),
  'classes' => 
  array (
    0 => 
    array (
      'name' => 'Class 8 - VIII',
      'status' => 'active',
    ),
    1 => 
    array (
      'name' => 'Class 9 - IX',
      'status' => 'active',
    ),
    2 => 
    array (
      'name' => 'Class 10 - X',
      'status' => 'active',
    ),
    3 => 
    array (
      'name' => 'Class 11 - XI',
      'status' => 'active',
    ),
    4 => 
    array (
      'name' => 'Class 12 - XII',
      'status' => 'active',
    ),
    5 => 
    array (
      'name' => 'Class 7 - VII',
      'status' => 'active',
    ),
  ),
  'subjects' => 
  array (
    0 => 
    array (
      'name' => 'Physics',
      'status' => 'active',
    ),
    1 => 
    array (
      'name' => 'Chemistry',
      'status' => 'active',
    ),
    2 => 
    array (
      'name' => 'Biology',
      'status' => 'active',
    ),
    3 => 
    array (
      'name' => 'Science',
      'status' => 'active',
    ),
  ),
  'batches' => 
  array (
    0 => 
    array (
      'name' => 'IXDRMC1',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    1 => 
    array (
      'name' => 'IXDRMC2',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    2 => 
    array (
      'name' => 'IXDRMC3',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'tue',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
        1 => 
        array (
          'day' => 'thu',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    3 => 
    array (
      'name' => 'IXDRMC4',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '10:00',
          'end_time' => '11:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '10:00',
          'end_time' => '11:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    4 => 
    array (
      'name' => 'IXDRMCV',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'mon',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
        1 => 
        array (
          'day' => 'wed',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    5 => 
    array (
      'name' => 'XDRMC1',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '11:00',
          'end_time' => '12:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '11:00',
          'end_time' => '12:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    6 => 
    array (
      'name' => 'XDRMC2',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    7 => 
    array (
      'name' => 'XDRMC3',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'sun',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
        1 => 
        array (
          'day' => 'tue',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    8 => 
    array (
      'name' => 'XIDRMC1',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '08:00',
          'end_time' => '09:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '08:00',
          'end_time' => '09:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    9 => 
    array (
      'name' => 'XIDRMC2',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    10 => 
    array (
      'name' => 'XDRMC1-26',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    11 => 
    array (
      'name' => 'XDRMC2-26',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Chemistry',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
      ),
    ),
    12 => 
    array (
      'name' => 'IXA',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'mon',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
        1 => 
        array (
          'day' => 'wed',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    13 => 
    array (
      'name' => 'IXB',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    14 => 
    array (
      'name' => 'IXC',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'mon',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
        1 => 
        array (
          'day' => 'wed',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    15 => 
    array (
      'name' => 'IXD',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '19:00',
          'end_time' => '20:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '19:00',
          'end_time' => '20:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    16 => 
    array (
      'name' => 'IXE',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '11:00',
          'end_time' => '12:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '11:00',
          'end_time' => '12:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    17 => 
    array (
      'name' => 'IXF',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'sun',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
        1 => 
        array (
          'day' => 'tue',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    18 => 
    array (
      'name' => 'IXV',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'sun',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
        1 => 
        array (
          'day' => 'thu',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    19 => 
    array (
      'name' => 'XA',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'sun',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
        1 => 
        array (
          'day' => 'tue',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    20 => 
    array (
      'name' => 'XB',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '10:00',
          'end_time' => '11:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '10:00',
          'end_time' => '11:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    21 => 
    array (
      'name' => 'XC',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    22 => 
    array (
      'name' => 'XD',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    23 => 
    array (
      'name' => 'XIA',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '15:00',
          'end_time' => '16:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '15:00',
          'end_time' => '16:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    24 => 
    array (
      'name' => 'XIB',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    25 => 
    array (
      'name' => 'XIA-26',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    26 => 
    array (
      'name' => 'XIB-26',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Physics',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
      ),
      'teacher_emails' => 
      array (
        0 => 'sanowar@email.com',
      ),
    ),
    27 => 
    array (
      'name' => 'IXB1',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    28 => 
    array (
      'name' => 'IXB2',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    29 => 
    array (
      'name' => 'IXB3',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'tue',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
        1 => 
        array (
          'day' => 'thu',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    30 => 
    array (
      'name' => 'IXB4',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'mon',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
        1 => 
        array (
          'day' => 'wed',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    31 => 
    array (
      'name' => 'IXB5',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'mon',
          'start_time' => '15:00',
          'end_time' => '16:00',
        ),
        1 => 
        array (
          'day' => 'wed',
          'start_time' => '15:00',
          'end_time' => '16:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    32 => 
    array (
      'name' => 'IXB6',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '09:00',
          'end_time' => '10:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '09:00',
          'end_time' => '10:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    33 => 
    array (
      'name' => 'IXBV1',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '10:00',
          'end_time' => '11:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '10:00',
          'end_time' => '11:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    34 => 
    array (
      'name' => 'IXBV2',
      'class_name' => 'Class 9 - IX',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    35 => 
    array (
      'name' => 'XB1',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '11:00',
          'end_time' => '12:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '11:00',
          'end_time' => '12:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    36 => 
    array (
      'name' => 'XB2',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '17:00',
          'end_time' => '18:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    37 => 
    array (
      'name' => 'XBV',
      'class_name' => 'Class 10 - X',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'sun',
          'start_time' => '15:00',
          'end_time' => '16:00',
        ),
        1 => 
        array (
          'day' => 'tue',
          'start_time' => '15:00',
          'end_time' => '16:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    38 => 
    array (
      'name' => 'XIB1',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'sun',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
        1 => 
        array (
          'day' => 'tue',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    39 => 
    array (
      'name' => 'XIB2',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'tue',
          'start_time' => '19:00',
          'end_time' => '20:00',
        ),
        1 => 
        array (
          'day' => 'thu',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    40 => 
    array (
      'name' => 'XIB4',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'wed',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
        1 => 
        array (
          'day' => 'thu',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    41 => 
    array (
      'name' => 'XIB1-26',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'fri',
          'start_time' => '08:00',
          'end_time' => '09:00',
        ),
        1 => 
        array (
          'day' => 'sat',
          'start_time' => '08:00',
          'end_time' => '09:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    42 => 
    array (
      'name' => 'XIB2-26',
      'class_name' => 'Class 11 - XI',
      'subject_name' => 'Biology',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'single',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'sun',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
        1 => 
        array (
          'day' => 'tue',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'salek@email.com',
      ),
    ),
    43 => 
    array (
      'name' => 'VIIIB1',
      'class_name' => 'Class 8 - VIII',
      'subject_name' => 'Science',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'equal',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'mon',
          'start_time' => '16:00',
          'end_time' => '17:00',
        ),
        1 => 
        array (
          'day' => 'wed',
          'start_time' => '15:00',
          'end_time' => '17:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
        1 => 'sanowar@email.com',
        2 => 'salek@email.com',
      ),
    ),
    44 => 
    array (
      'name' => 'VIIIB2',
      'class_name' => 'Class 8 - VIII',
      'subject_name' => 'Science',
      'monthly_fee' => 1500.0,
      'distribution_type' => 'equal',
      'status' => 'active',
      'schedule_slots' => 
      array (
        0 => 
        array (
          'day' => 'sat',
          'start_time' => '16:00',
          'end_time' => '18:00',
        ),
        1 => 
        array (
          'day' => 'mon',
          'start_time' => '18:00',
          'end_time' => '19:00',
        ),
      ),
      'teacher_emails' => 
      array (
        0 => 'masum@email.com',
        1 => 'sanowar@email.com',
        2 => 'salek@email.com',
      ),
    ),
  ),
);

        foreach ($data['users'] as $row) {
            $user = User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'username' => $row['username'],
                    'password' => Hash::make($row['password']),
                    'status' => $row['status'],
                    'email_verified_at' => now(),
                ],
            );

            if (! empty($row['role'])) {
                $role = Role::query()->where('name', $row['role'])->first();

                if ($role) {
                    $user->syncRoles([$role->name]);
                }
            }
        }

        foreach ($data['teachers'] as $row) {
            $user = User::query()->where('email', $row['user_email'])->first();

            if (! $user) {
                continue;
            }

            Teacher::updateOrCreate(
                ['user_id' => $user->id],
                ['status' => $row['status']],
            );
        }

        foreach ($data['classes'] as $row) {
            AcademicClass::updateOrCreate(
                ['name' => $row['name']],
                ['status' => $row['status']],
            );
        }

        foreach ($data['subjects'] as $row) {
            Subject::updateOrCreate(
                ['name' => $row['name']],
                ['status' => $row['status']],
            );
        }

        foreach ($data['batches'] as $row) {
            $class = AcademicClass::query()->where('name', $row['class_name'])->first();
            $subject = ! empty($row['subject_name'])
                ? Subject::query()->where('name', $row['subject_name'])->first()
                : null;

            if (! $class) {
                continue;
            }

            $batch = Batch::updateOrCreate(
                ['name' => $row['name']],
                [
                    'class_id' => $class->id,
                    'subject_id' => $subject?->id,
                    'monthly_fee' => $row['monthly_fee'],
                    'distribution_type' => $row['distribution_type'],
                    'schedule_slots' => $row['schedule_slots'],
                    'status' => $row['status'],
                ],
            );

            $teacherIds = Teacher::query()
                ->whereHas('user', fn ($query) => $query->whereIn('email', $row['teacher_emails'] ?? []))
                ->pluck('id')
                ->all();

            $batch->teachers()->sync($teacherIds);
        }
    }
}