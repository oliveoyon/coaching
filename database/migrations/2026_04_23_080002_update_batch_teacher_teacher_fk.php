<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $teacherUserIds = DB::table('users')
            ->join('model_has_roles', function ($join) {
                $join->on('users.id', '=', 'model_has_roles.model_id')
                    ->where('model_has_roles.model_type', 'App\\Models\\User');
            })
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'Teacher')
            ->select('users.id as user_id', 'users.status')
            ->get();

        foreach ($teacherUserIds as $teacherUser) {
            DB::table('teachers')->updateOrInsert(
                ['user_id' => $teacherUser->user_id],
                [
                    'status' => $teacherUser->status,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        $existingBatchTeachers = DB::table('batch_teacher')->get();

        Schema::table('batch_teacher', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        foreach ($existingBatchTeachers as $batchTeacher) {
            $teacherId = DB::table('teachers')
                ->where('user_id', $batchTeacher->teacher_id)
                ->value('id');

            if ($teacherId) {
                DB::table('batch_teacher')
                    ->where('id', $batchTeacher->id)
                    ->update(['teacher_id' => $teacherId]);
            }
        }

        Schema::table('batch_teacher', function (Blueprint $table) {
            $table->foreign('teacher_id')
                ->references('id')
                ->on('teachers')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('batch_teacher', function (Blueprint $table) {
            $table->dropForeign(['teacher_id']);
        });

        Schema::table('batch_teacher', function (Blueprint $table) {
            $table->foreign('teacher_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};
