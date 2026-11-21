<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->date('default_start_date');
            $table->unsignedSmallInteger('duration_days');
            $table->json('objectives');
            $table->boolean('is_default')->default(false);
            $table->boolean('accepting_participants')->default(true);
            $table->timestamps();
        });
        $id = DB::table('challenges')->insertGetId([
            'name' => 'Desafio da Virada', 'default_start_date' => '2026-11-09', 'duration_days' => 30,
            'objectives' => json_encode(['movement', 'water', 'sleep', 'strength', 'skill', 'plus']),
            'is_default' => true, 'accepting_participants' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
        Schema::create('challenge_feature_settings', function (Blueprint $table) {
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->string('feature');
            $table->string('mode')->default('calendar');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->primary(['challenge_id', 'feature']);
        });
        Schema::table('users', fn (Blueprint $table) => $table->foreignId('active_challenge_id')->nullable()->constrained('challenges')->restrictOnDelete());
        DB::table('users')->update(['active_challenge_id' => $id]);
        Schema::create('challenge_participations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->timestamps();
            $table->unique(['user_id', 'challenge_id']);
        });
        DB::table('users')->orderBy('id')->chunkById(200, function ($users) use ($id) {
            DB::table('challenge_participations')->insert($users->map(fn ($user) => [
                'challenge_id' => $id, 'user_id' => $user->id, 'start_date' => '2026-11-09',
                'created_at' => now(), 'updated_at' => now(),
            ])->all());
        });
        foreach (['athlete_profiles', 'checkins', 'achievements', 'private_comments'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->foreignId('challenge_id')->nullable()->constrained()->restrictOnDelete());
            DB::table($name)->update(['challenge_id' => $id]);
        }
        Schema::table('athlete_profiles', function (Blueprint $table) {
            $table->index('user_id', 'athlete_profiles_user_lookup');
            $table->dropUnique(['user_id']);
            $table->unique(['user_id', 'challenge_id']);
        });
        Schema::table('checkins', function (Blueprint $table) {
            $table->index('user_id', 'checkins_user_lookup');
            $table->dropUnique(['user_id', 'day']);
            $table->unique(['user_id', 'challenge_id', 'day']);
        });
    }

    public function down(): void
    {
        // Conservar históricos de múltiplos desafios: não reduzir índices a um desafio.
        throw new RuntimeException('Esta migração preserva históricos por desafio e não pode ser revertida automaticamente.');
    }
};
