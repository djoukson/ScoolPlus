<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        if (!Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('must_change_password')->default(false);
            });
        }

        DB::table('users')
            ->select(['id', 'password'])
            ->orderBy('id')
            ->chunkById(200, function ($users): void {
                foreach ($users as $user) {
                    if (Hash::check('sp12345$', $user->password)) {
                        DB::table('users')
                            ->where('id', $user->id)
                            ->update(['must_change_password' => true]);
                    }
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'must_change_password')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('must_change_password');
            });
        }
    }
};
