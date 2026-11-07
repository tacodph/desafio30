<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groups = DB::table('food_groups')->where('category', '1. Proteínas')->pluck('id', 'name');

        foreach ([
            'Atum fresco' => 'Atum fresco (ou atum em lata, em conserva em água)',
            'Sardinha fresca' => 'Sardinha fresca (ou em lata)',
        ] as $old => $new) {
            DB::table('foods')->where('food_group_id', $groups->get('Peixes e frutos do mar'))
                ->where('name', $old)->update(['name' => $new, 'updated_at' => now()]);
        }

        $group = $groups->get('Miúdos (opcionais)');
        DB::table('food_groups')->where('id', $group)->update(['name' => 'Miúdos', 'updated_at' => now()]);
        DB::table('foods')->where('food_group_id', $group)->where('name', 'Coração')
            ->update(['active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Preserva as edições posteriores feitas no catálogo.
    }
};
