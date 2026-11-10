<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Skill;
use App\Models\User;
use App\Models\UserType;
use App\Support\ChallengeSheet;
use App\Support\DayLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class SemanaUmTesteSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('Este seeder só pode ser executado no ambiente local.');
        }

        $email = 'atleta.semana1@desafio30.test';
        $name = 'Atleta Teste · Semana 1';

        DB::transaction(function () use ($email, $name) {
            $athlete = User::query()->firstOrNew(['email' => $email]);

            if ($athlete->exists && ($athlete->name !== $name || ! $athlete->isAthlete())) {
                throw new RuntimeException('O e-mail de teste já pertence a outra conta.');
            }

            $athlete->forceFill([
                'name' => $name,
                'password' => 'ViradaTeste2026!',
                'user_type_id' => UserType::idFor(UserRole::Atleta),
                'email_verified_at' => $athlete->email_verified_at ?? now(),
                'challenge_courtesy_at' => $athlete->challenge_courtesy_at ?? now(),
                'challenge_early_access' => true,
                'welcome_acknowledged_at' => $athlete->welcome_acknowledged_at ?? now(),
            ])->save();

            $skill = Skill::query()->orderBy('position')->orderBy('id')->value('name') ?? 'Planejar o cardápio';

            $profile = $athlete->athleteProfile()->updateOrCreate([], [
                'weight' => 72,
                'waist' => 79,
                'abdomen' => 84,
                'hip' => 98,
                'movement_level' => 'intermediario',
                'movement_minutes' => 30,
                'water_liters' => 2.5,
                'sleep_goal' => '7h por noite',
                'weekly_skills' => [[$skill]],
                'welcomed_at' => now(),
                'goals_set_at' => now(),
            ]);

            $week = [
                ['Musculação', 40, 2600, 7.5, true, true, null],
                ['Corrida', 35, 2500, 7.0, true, true, null],
                ['Caminhada', 25, 2200, 7.5, true, true, null],
                ['Funcional', 45, 2800, 6.5, true, true, null],
                ['Bike', 40, 2600, 8.0, true, false, null],
                ['Yoga', 30, 2500, 7.0, false, true, 'Pizza com amigos'],
                ['Dança', 35, 2700, 8.0, true, false, 'Almoço em família'],
            ];

            $mealNotes = [
                1 => 'Frango grelhado, arroz, feijão e salada no almoço.',
                3 => 'Organizei as refeições da semana e deixei os legumes preparados.',
                6 => 'Mantive as refeições planejadas e comi pizza com amigos no jantar.',
            ];
            $mealPhotos = [1 => 'self-service.png', 3 => 'pratoComPercentual.png'];
            $disk = Storage::disk('public');

            foreach ($week as $index => [$workout, $minutes, $water, $sleep, $noFastFood, $noSweet, $luxuryMeal]) {
                $details = DayLog::normalize([
                    'workouts' => [['type' => $workout, 'minutes' => $minutes]],
                    'water_ml' => $water,
                    'sleep_hours' => $sleep,
                    'food' => [
                        'no_alcohol' => true,
                        'no_fast_food' => $noFastFood,
                        'no_sweet' => $noSweet,
                        'luxury' => $luxuryMeal !== null,
                    ],
                    'skill' => $skill,
                ]);

                $day = $index + 1;
                $checkin = $athlete->checkins()->updateOrCreate(['day' => $day], [
                    ...DayLog::achieved($details, $profile),
                    'checked_in' => true,
                    'details' => $details,
                    'plus_count' => (int) ($luxuryMeal !== null),
                    'notes' => ['strength' => $mealNotes[$day] ?? '', 'plus' => $luxuryMeal ?? ''],
                ]);

                if (isset($mealPhotos[$day])) {
                    $filename = $mealPhotos[$day];
                    $path = 'meals/seed-semana1-'.$filename;
                    $contents = file_get_contents(public_path('images/alimentacao/'.$filename));
                    if ($contents === false || ! $disk->put($path, $contents)) {
                        throw new RuntimeException('Não foi possível gravar a foto de refeição de teste.');
                    }

                    $checkin->forceFill(['meal_photos' => [$path]])->save();
                }
            }

            foreach ([
                ['cantinho', 1, 'Preparei meu almoço e consegui seguir o planejamento.'],
                ['resistido', 3, 'Recusei o refrigerante e escolhi água.'],
                ['performance', 4, 'Completei o treino funcional de 45 minutos.'],
                ['cantinho', 7, 'Planejei as compras para a próxima semana.'],
            ] as [$type, $day, $detail]) {
                $athlete->achievements()->updateOrCreate(
                    ['type' => $type, 'achieved_on' => ChallengeSheet::dateForDay($day)],
                    ['detail' => $detail],
                );
            }
        });

        $this->command?->info(sprintf(
            'Check-ins, conquistas, refeições luxo e fotos de %s a %s gravados para %s.',
            ChallengeSheet::dateForDay(1),
            ChallengeSheet::dateForDay(7),
            $email,
        ));
    }
}
