<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $groups = DB::table('food_groups')->where('category', '1. Proteínas')->pluck('id', 'name');

        foreach ([
            'Carnes bovinas' => [
                'Patinho' => 'Patinho bovino sem gordura',
                'Lagarto' => 'Lagarto bovino',
                'Coxão mole' => 'Coxão mole sem gordura',
                'Maminha' => 'Maminha sem gordura',
                'Músculo' => 'Músculo bovino sem gordura',
                'Paleta bovina' => 'Paleta bovina sem gordura',
            ],
            'Frango e outras aves' => [
                'Peito de frango sem pele' => 'Peito de frango sem pele',
                'Filé de frango' => 'Filé de frango',
                'Peru' => 'Peito de peru sem pele (não embutido)',
                'Coxa de frango' => 'Coxa de frango sem pele',
                'Sobrecoxa de frango' => 'Sobrecoxa de frango sem pele',
            ],
        ] as $group => $foods) {
            $groupId = $groups->get($group);

            if (! $groupId) {
                continue;
            }

            $position = 0;
            foreach ($foods as $previous => $name) {
                $query = DB::table('foods')->where('food_group_id', $groupId);
                $foodId = (clone $query)->where('name', $name)->value('id')
                    ?? (clone $query)->where('name', $previous)->value('id');
                $values = ['name' => $name, 'position' => ++$position, 'active' => true, 'updated_at' => now()];

                if ($foodId) {
                    $query->where('id', $foodId)->update($values);
                } else {
                    DB::table('foods')->insert([...$values, 'food_group_id' => $groupId, 'created_at' => now()]);
                }
            }

            DB::table('foods')->where('food_group_id', $groupId)->whereNotIn('name', array_values($foods))
                ->update(['active' => false, 'updated_at' => now()]);
        }

        DB::table('food_groups')->where('id', $groups->get('Frango e outras aves'))->update([
            'notes' => json_encode(['Coxa e sobrecoxa de frango sem pele: consuma no máximo duas vezes por semana.'], JSON_THROW_ON_ERROR),
            'updated_at' => now(),
        ]);

        DB::table('foods')->where('food_group_id', $groups->get('Carne suína'))->where('name', 'Pernil')
            ->update(['active' => false, 'updated_at' => now()]);
    }

    public function down(): void
    {
        // Preserva as edições que o administrador fez no catálogo após esta atualização.
    }
};
