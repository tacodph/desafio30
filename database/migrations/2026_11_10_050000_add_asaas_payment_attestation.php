<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('challenge_access_types')->insert([
            'code' => 'asaas_attested',
            'label' => 'Asaas pago, aguardando confirmação',
        ]);

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('asaas_attested_at')->nullable();
            $table->foreignId('asaas_attested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('asaas_attestation_url')->nullable();
        });
    }

    public function down(): void
    {
        DB::table('users')->where('challenge_access_type_code', 'asaas_attested')->update(['challenge_access_type_code' => null]);
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['asaas_attested_by']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['asaas_attested_at', 'asaas_attested_by', 'asaas_attestation_url']);
        });
        DB::table('challenge_access_types')->where('code', 'asaas_attested')->delete();
    }
};
