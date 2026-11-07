<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groups = DB::table('food_groups')->where('category', '2. Laticínios e queijos')->pluck('id', 'name');

        DB::table('food_groups')->where('id', $groups->get('Iogurtes e leites'))->update([
            'notes' => json_encode(['Confira no Cantinho da Nutri sugestões de marcas.'], JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);

        $cheeses = $groups->get('Queijos e cremes');

        if (! $cheeses) {
            return;
        }

        DB::table('food_groups')->where('id', $cheeses)->update([
            'name' => 'Queijos',
            'notes' => json_encode([
                'Os queijos estão por ordem aproximada de calorias e quantidade de gordura. Mantenha queijos mais magros na maior parte da semana e escolha os queijos mais gordurosos para variar sua alimentação duas a três vezes por semana.',
            ], JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);

        $foods = DB::table('foods')->where('food_group_id', $cheeses);
        foreach ([
            'Cottage', 'Ricota', 'Queijo minas frescal', 'Queijo minas padrão', 'Meia-cura',
            'Gorgonzola', 'Muçarela', 'Queijo coalho', 'Queijo prato', 'Queijo canastra', 'Parmesão',
        ] as $index => $name) {
            (clone $foods)->where('name', $name)->update(['position' => $index + 1, 'updated_at' => now()]);
        }

        $foods->whereIn('name', ['Requeijão', 'Cream cheese', 'Creme de ricota', 'Outro: ____________________'])
            ->update(['active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Preserva as edições posteriores feitas no catálogo.
    }
};
