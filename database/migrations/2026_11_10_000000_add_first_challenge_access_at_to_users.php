<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('first_challenge_access_at')->nullable();
        });

        DB::table('users')->whereNotNull('welcome_acknowledged_at')
            ->update(['first_challenge_access_at' => DB::raw('welcome_acknowledged_at')]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('first_challenge_access_at');
        });
    }
};
