<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $group = DB::table('food_groups')->where('category', '8. Itens de despensa e complementos')->where('name', '')->first();

        if (! $group) {
            return;
        }

        $foods = DB::table('foods')->where('food_group_id', $group->id);
        foreach ([
            'Leite de coco' => 'Coco ralado sem açúcar',
            'Chá' => 'Chá de folhas ou sachê',
            'Milho em conserva' => 'Milho congelado',
        ] as $old => $new) {
            (clone $foods)->where('name', $old)->update(['name' => $new, 'updated_at' => now()]);
        }

        $foods->where('name', 'Atum ou sardinha em lata')->update(['active' => false, 'updated_at' => now()]);

        $notes = json_decode($group->notes ?? '[]', true, flags: JSON_THROW_ON_ERROR) ?? [];
        $notes[] = 'Evite chás adoçados.';
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
