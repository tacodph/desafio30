<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $pantry = DB::table('food_groups')->where('category', '8. Itens de despensa e complementos')->where('name', '')->first();

        if ($pantry) {
            $foods = DB::table('foods')->where('food_group_id', $pantry->id);

            foreach ([
                'Cacau em pó' => 'Cacau em pó 100%',
                'Molho de tomate' => 'Passata de tomate',
                'Adoçante ou açúcar habitual' => 'Adoçante',
            ] as $previous => $name) {
                (clone $foods)->where('name', $previous)->update(['name' => $name, 'active' => true, 'updated_at' => now()]);
            }

            $foods->whereIn('name', ['Chocolate', 'Vinagre', 'Ervilha em conserva', 'Mostarda'])
                ->update(['active' => false, 'updated_at' => now()]);

            $this->appendNotes($pantry, ['Sugestão de adoçante natural: estévia, taumatina ou polióis, como xilitol e eritritol.']);
        }

        $practical = DB::table('food_groups')->where('category', '9. Opções práticas para dias corridos')->where('name', '')->first();

        if (! $practical) {
            return;
        }

        $items = [
            'Vegetais congelados',
            'Frutas congeladas',
            'Vegetais já higienizados ou cortados',
            'Bandejas práticas de vegetais',
            'Salada pronta e higienizada',
            'Frango desfiado',
        ];

        foreach ($items as $index => $name) {
            DB::table('foods')->updateOrInsert(
                ['food_group_id' => $practical->id, 'name' => $name],
                fn (bool $exists) => [
                    'position' => $index + 1,
                    'active' => true,
                    'updated_at' => now(),
                    ...($exists ? [] : ['created_at' => now()]),
                ],
            );
        }

        DB::table('foods')->where('food_group_id', $practical->id)->whereNotIn('name', $items)
            ->update(['active' => false, 'updated_at' => now()]);

        $this->appendNotes($practical, [
            'Confira no Cantinho da Nutri sugestões de marcas.',
            'Confira também no Cantinho da Nutri opções de iFood e refeições prontas.',
        ]);
    }

    private function appendNotes(object $group, array $notes): void
    {
        $current = json_decode($group->notes ?? '[]', true, flags: JSON_THROW_ON_ERROR) ?? [];

        DB::table('food_groups')->where('id', $group->id)->update([
            'notes' => json_encode(array_values(array_unique([...$current, ...$notes])), JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Preserva as edições posteriores feitas no catálogo.
    }
};
