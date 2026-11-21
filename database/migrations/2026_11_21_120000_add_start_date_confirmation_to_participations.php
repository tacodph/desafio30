<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('challenge_participations', fn (Blueprint $table) => $table->timestamp('start_date_confirmed_at')->nullable());
        DB::table('challenge_participations')->whereExists(function ($query) {
            $query->selectRaw('1')->from('checkins')
                ->whereColumn('checkins.user_id', 'challenge_participations.user_id')
                ->whereColumn('checkins.challenge_id', 'challenge_participations.challenge_id');
        })->update(['start_date_confirmed_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('challenge_participations', fn (Blueprint $table) => $table->dropColumn('start_date_confirmed_at'));
    }
};
