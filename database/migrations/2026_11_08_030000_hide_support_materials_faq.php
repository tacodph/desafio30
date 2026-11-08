<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('faqs')->where('question', 'Vou receber materiais de apoio?')
            ->update(['published' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('faqs')->where('question', 'Vou receber materiais de apoio?')
            ->update(['published' => true, 'updated_at' => now()]);
    }
};
