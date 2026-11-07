<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $group = DB::table('food_groups')->where('category', '2. Laticínios e queijos')->where('name', 'Queijos')->first();

        if (! $group) {
            return;
        }

        foreach ([
            'Cottage', 'Ricota', 'Queijo minas frescal', 'Muçarela', 'Queijo minas padrão', 'Queijo coalho',
            'Meia-cura', 'Queijo prato', 'Queijo canastra', 'Gorgonzola', 'Parmesão',
        ] as $index => $name) {
            DB::table('foods')->where('food_group_id', $group->id)->where('name', $name)
                ->update(['position' => $index + 1, 'updated_at' => now()]);
        }

        $notes = json_decode($group->notes ?? '[]', true, flags: JSON_THROW_ON_ERROR) ?? [];
        $notes[] = 'A ordem é aproximada, pois o teor de gordura pode variar conforme a marca, o fabricante e o tempo de maturação do queijo. Em geral, queijos mais frescos e com maior teor de umidade tendem a apresentar menos gordura, enquanto os mais curados e maturados são mais concentrados.';

        DB::table('food_groups')->where('id', $group->id)->update([
            'notes' => json_encode(array_values(array_unique($notes)), JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Preserva as edições posteriores feitas no catálogo.
    }
};
