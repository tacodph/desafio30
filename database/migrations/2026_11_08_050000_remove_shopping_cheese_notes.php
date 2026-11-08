<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $removed = [
            'Os queijos estão por ordem aproximada de calorias e quantidade de gordura. Mantenha queijos mais magros na maior parte da semana e escolha os queijos mais gordurosos para variar sua alimentação duas a três vezes por semana.',
            'A ordem é aproximada, pois o teor de gordura pode variar conforme a marca, o fabricante e o tempo de maturação do queijo. Em geral, queijos mais frescos e com maior teor de umidade tendem a apresentar menos gordura, enquanto os mais curados e maturados são mais concentrados.',
        ];

        foreach (DB::table('food_groups')->where('category', '2. Laticínios e queijos')->whereIn('name', ['Queijos', 'Queijos e cremes'])->get() as $group) {
            $notes = json_decode($group->notes ?? '[]', true, flags: JSON_THROW_ON_ERROR) ?? [];
            $remaining = array_values(array_filter($notes, fn ($note) => ! in_array($note, $removed, true)));
            if ($remaining !== $notes) {
                DB::table('food_groups')->where('id', $group->id)->update(['notes' => json_encode($remaining, JSON_THROW_ON_ERROR), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // Preserva as edições posteriores feitas nas observações do catálogo.
    }
};
