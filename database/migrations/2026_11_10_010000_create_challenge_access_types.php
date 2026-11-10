<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_access_types', function (Blueprint $table) {
            $table->string('code', 24)->primary();
            $table->string('label');
        });

        DB::table('challenge_access_types')->insert([
            ['code' => 'asaas', 'label' => 'Pagamento Asaas'],
            ['code' => 'courtesy', 'label' => 'Cortesia'],
            ['code' => 'external', 'label' => 'Pagamento externo'],
            ['code' => 'legacy', 'label' => 'Acesso legado'],
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->string('challenge_access_type_code', 24)->nullable();
            $table->foreign('challenge_access_type_code')->references('code')->on('challenge_access_types');
        });

        DB::table('users')->whereNotNull('challenge_paid_at')->update(['challenge_access_type_code' => 'legacy']);
        DB::table('users')->whereNotNull('challenge_paid_at')->whereExists(function ($query) {
            $query->selectRaw('1')->from('challenge_payments')->whereColumn('challenge_payments.user_id', 'users.id')
                ->whereNotNull('asaas_payment_id')->whereIn('status', ['CONFIRMED', 'RECEIVED']);
        })->update(['challenge_access_type_code' => 'asaas']);
        DB::table('users')->whereNotNull('challenge_courtesy_at')->update(['challenge_access_type_code' => 'courtesy']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['challenge_access_type_code']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('challenge_access_type_code');
        });
        Schema::dropIfExists('challenge_access_types');
    }
};
