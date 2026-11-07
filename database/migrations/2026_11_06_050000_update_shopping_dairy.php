<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $group = DB::table('food_groups')->where('category', '2. Laticínios e queijos')
            ->where('name', 'Iogurtes e leites')->first();

        if (! $group) {
            return;
        }

        $items = [
            'Iogurte natural integral',
            'Iogurte natural desnatado',
            'Coalhada',
            'Leite pasteurizado',
            'Leite em pó integral',
            'Leite em pó desnatado',
            'Iogurte proteico',
            'Bebidas proteicas',
        ];

        foreach ($items as $index => $name) {
            DB::table('foods')->updateOrInsert(
                ['food_group_id' => $group->id, 'name' => $name],
                fn (bool $exists) => [
                    'position' => $index + 1,
                    'active' => true,
                    'updated_at' => now(),
                    ...($exists ? [] : ['created_at' => now()]),
                ],
            );
        }

        DB::table('foods')->where('food_group_id', $group->id)->whereIn('name', [
            'Iogurte natural', 'Iogurte natural tipo skyr', 'Leite', 'Leite em pó',
            'Bebida láctea ou alternativa habitual: ____________________',
        ])->update(['active' => false, 'updated_at' => now()]);

        $notes = json_decode($group->notes ?? '[]', true, flags: JSON_THROW_ON_ERROR) ?? [];
        $notes[] = 'Confira no Cantinho da Nutri sugestões de marcas.';
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
