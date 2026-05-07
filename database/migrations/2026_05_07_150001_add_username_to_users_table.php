<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 50)->nullable()->unique()->after('name');
        });

        User::query()->orderBy('id')->get()->each(function (User $user): void {
            if (filled($user->username)) {
                return;
            }

            $base = Str::slug((string) ($user->name ?: Str::before((string) $user->email, '@')), '');
            $base = $base !== '' ? Str::lower($base) : 'user';
            $username = Str::limit($base, 40, '');
            $candidate = $username;
            $suffix = 1;

            while (
                User::query()
                    ->where('id', '!=', $user->id)
                    ->where('username', $candidate)
                    ->exists()
            ) {
                $candidate = Str::limit($username, 40, '').$suffix;
                $suffix++;
            }

            $user->forceFill(['username' => $candidate])->saveQuietly();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
        });
    }
};
