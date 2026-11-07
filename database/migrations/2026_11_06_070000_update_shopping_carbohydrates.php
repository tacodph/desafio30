<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groups = DB::table('food_groups')->where('category', '3. Carboidratos, cereais e raízes')->pluck('id', 'name');
        $group = $groups->get('Cereais, pães e massas');

        if (! $group) {
            return;
        }

        $items = [
            'Arroz branco',
            'Arroz integral',
            'Aveia (flocos finos, flocos grossos ou farelo de aveia)',
            'Pão de forma integral',
            'Macarrão de arroz ou de milho',
            'Macarrão integral',
            'Cuscuz de milho',
            'Cuscuz de arroz',
            'Goma de tapioca',
            'Biscoito de arroz',
            'Milho',
        ];

        foreach ($items as $index => $name) {
            DB::table('foods')->updateOrInsert(
                ['food_group_id' => $group, 'name' => $name],
                fn (bool $exists) => [
                    'position' => $index + 1,
                    'active' => true,
                    'updated_at' => now(),
                    ...($exists ? [] : ['created_at' => now()]),
                ],
            );
        }

        DB::table('foods')->whereIn('food_group_id', $groups->values())
            ->where(fn ($query) => $query->where('food_group_id', '!=', $group)->orWhereNotIn('name', $items))
            ->update(['active' => false, 'updated_at' => now()]);

        DB::table('food_groups')->whereIn('id', $groups->values())
            ->update(['category' => '3. Carboidratos', 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Preserva as edições posteriores feitas no catálogo.
    }
};
