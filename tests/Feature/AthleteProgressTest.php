<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AthleteProgressTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Calendar restrictions have their own feature suite; isolate progress calculations here.
        $this->app->bind(\App\Support\ChallengeAccess::class, fn () => new class extends \App\Support\ChallengeAccess
        {
            public function pageOpen(string $feature): bool { return true; }
            public function dayEditable(int $day): bool { return $day >= 1 && $day <= 30; }
        });
    }

    public function test_challenge_has_thirty_days_and_rejects_day_thirty_one(): void
    {
        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)
            ->get('/atleta/evolucao/dias')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('days', 30)
                ->has('weeks', 5)
                ->where('days.0.date', '09/11')
                ->where('days.6.week', 1)
                ->where('days.7.week', 2)
                ->where('days.28.week', 5)
                ->where('days.29.day', 30)
                ->where('days.29.date', '08/12'));

        $this->actingAs($athlete)
            ->put('/atleta/dias', ['days' => [['day' => 31]]])
            ->assertSessionHasErrors('days.0.day');
    }

    public function test_only_an_athlete_can_fill_the_progress(): void
    {
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create())
            ->get('/atleta/evolucao/dias')
            ->assertOk()->assertInertia(fn (Assert $page) => $page->where('canEdit', false)->where('adminPreview', true));
        $this->put('/atleta/dias', ['days' => [['day' => 1]]])->assertForbidden();

        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)
            ->get('/atleta/evolucao/dias')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard/Evolucao')->where('total', 0));

        $this->actingAs($athlete)
            ->put('/atleta/metas', [
                'movement_level' => 'iniciante',
                'movement_minutes' => 30,
                'water_liters' => 2.5,
                'sleep_goal' => '7h',
                'strength_goal' => 'prancha 60s',
                'penalty' => true,
            ])
            ->assertRedirect('/atleta/ponto-de-partida/metas');

        $this->assertDatabaseHas('athlete_profiles', [
            'user_id' => $athlete->id,
            'movement_level' => 'iniciante',
            'penalty' => true,
        ]);
    }

    public function test_week_one_points_include_daily_score_and_bonuses(): void
    {
        $athlete = $this->startedAthlete();
        $days = [];

        foreach ([1, 2, 3, 4, 5, 6] as $day) {
            $days[] = [
                'day' => $day,
                'checked_in' => true,
                'movement' => true,
                'water' => true,
                'sleep' => true,
                'strength' => true,
                'skill' => true,
                'plus_count' => $day === 1 ? 3 : 0,
                'notes' => '',
            ];
        }

        $this->actingAs($athlete)
            ->put('/atleta/dias', ['days' => $days])
            ->assertRedirect('/atleta/evolucao/dias');

        $this->actingAs($athlete)
            ->post('/atleta/conquistas', [
                'achieved_on' => '2026-11-09',
                'type' => 'first_time',
                'detail' => 'Primeira prancha',
            ])
            ->assertRedirect('/atleta/evolucao/conquistas');

        $this->actingAs($athlete)
            ->get('/atleta/evolucao/dias')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('weeks.0.daily', 60)
                ->where('weeks.0.plus_bonus', 10)
                ->where('weeks.0.consistency', 10)
                ->where('weeks.0.goals_bonus', 10)
                ->where('weeks.0.achievements', 5)
                ->where('weeks.0.athletes_done', 1)
                ->where('weeks.0.athletes_total', 1)
                ->where('total', 95));
    }

    public function test_a_conquest_can_be_saved_without_a_date(): void
    {
        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)
            ->post('/atleta/conquistas', [
                'type' => 'cantinho',
                'detail' => 'Fiz marmita na semana',
            ])
            ->assertRedirect('/atleta/evolucao/conquistas');

        $this->assertDatabaseHas('achievements', [
            'user_id' => $athlete->id,
            'type' => 'cantinho',
            'detail' => 'Fiz marmita na semana',
        ]);
    }

    public function test_an_athlete_can_edit_a_conquest_and_a_nutritionist_can_comment(): void
    {
        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)
            ->post('/atleta/conquistas', [
                'type' => 'cantinho',
                'detail' => 'Fiz marmita na semana',
            ])
            ->assertRedirect('/atleta/evolucao/conquistas');

        $achievement = $athlete->achievements()->first();

        $this->actingAs($athlete)
            ->put("/atleta/conquistas/{$achievement->id}", ['detail' => 'Marmita de frango'])
            ->assertRedirect('/atleta/evolucao/conquistas');

        $this->actingAs($athlete)
            ->put("/atleta/conquistas/{$achievement->id}/comentario", [
                'comment' => 'Não pode',
                'emotion' => '👏',
            ])
            ->assertForbidden();

        $nutri = User::factory()->ofRole(UserRole::Nutricionista)->create();

        $this->actingAs($athlete)
            ->get('/atleta/evolucao/dias')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('nutritionistName', $nutri->name));

        $this->actingAs($nutri)
            ->get("/equipe/atletas/{$athlete->id}/evolucao")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard/Evolucao')
                ->where('canComment', true)
                ->where('canEdit', false)
                ->where('achievements.0.detail', 'Marmita de frango'));

        $this->actingAs($nutri)
            ->put("/atleta/conquistas/{$achievement->id}/comentario", [
                'comment' => 'Continua assim',
                'emotion' => '👏',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('achievements', [
            'id' => $achievement->id,
            'detail' => 'Marmita de frango',
            'comment' => 'Continua assim',
            'emotion' => '👏',
            'nutritionist' => $nutri->name,
        ]);

        $this->actingAs(User::factory()->ofRole(UserRole::Coach)->create())
            ->get("/equipe/atletas/{$athlete->id}/evolucao")
            ->assertForbidden();
    }

    public function test_an_athlete_picks_three_different_skills_each_week(): void
    {
        $athlete = $this->startedAthlete();
        $marmita = 'Fazer marmitas para 3 dias da semana.';
        $pratos = 'Aprender a fazer 5 pratos saudáveis que nunca fez.';
        $salada = 'Aprender a fazer uma salada que realmente tenha vontade de comer.';
        $this->actingAs($athlete)
            ->put('/atleta/habilidades', ['week' => 1, 'picks' => [$marmita, $pratos, $salada]])
            ->assertRedirect('/atleta/evolucao/dias');

        $this->put('/atleta/habilidades', ['week' => 2, 'picks' => [$marmita, $pratos, $salada]])
            ->assertRedirect('/atleta/evolucao/dias');

        $saved = $athlete->fresh()->athleteProfile->weekly_skills;

        $this->assertSame([$marmita, $pratos, $salada], $saved[0]);
        $this->assertSame([$marmita, $pratos, $salada], $saved[1]);

        $this->actingAs($athlete)
            ->put('/atleta/habilidades', ['week' => 1, 'picks' => [$marmita, $marmita, $salada]])
            ->assertSessionHasErrors('picks.1');
    }

    public function test_an_athlete_registers_the_day_one_baseline_once(): void
    {
        Storage::fake('public');
        $athlete = User::factory()->create();

        $this->actingAs($athlete)->get('/dashboard')->assertRedirect('/atleta/ponto-de-partida/primeiro-dia');

        $this->actingAs($athlete)
            ->post('/atleta/ponto-de-partida/primeiro-dia', [
                'weight' => 70.5,
                'waist' => 80,
                'abdomen' => 85,
                'hip' => 98,
                'photo' => UploadedFile::fake()->image('antes.jpg'),
            ])
            ->assertRedirect('/atleta/ponto-de-partida/metas');

        $profile = $athlete->fresh()->athleteProfile;

        $this->assertNotNull($profile->welcomed_at);
        $this->assertSame('70.5', $profile->weight);
        $this->assertSame('80.0', $profile->waist);
        Storage::disk('public')->assertExists($profile->photo);

        $this->actingAs($athlete)->get('/dashboard')->assertRedirect('/atleta/ponto-de-partida/metas');

        $this->actingAs($athlete)->put('/atleta/metas', ['movement_level' => 'iniciante', 'water_liters' => 2.5])->assertRedirect('/dashboard');
        $this->actingAs($athlete)->get('/dashboard')->assertOk();
        $this->assertNotNull($athlete->fresh()->athleteProfile->goals_set_at);

        // Depois do primeiro acesso, as duas telas continuam abertas para ajustes.
        $this->actingAs($athlete)->get('/atleta/ponto-de-partida/primeiro-dia')->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboard/PrimeiroDia')->where('firstAccess', false));
        $this->actingAs($athlete)->get('/atleta/ponto-de-partida/metas')->assertOk()->assertInertia(fn (Assert $page) => $page->component('dashboard/Metas')->where('firstAccess', false));
    }

    public function test_a_new_shopping_list_is_created_on_its_own_page_without_repeating_weeks(): void
    {
        $athlete = User::factory()->create();

        $this->actingAs($athlete)
            ->get('/objetivos/alimentacao/compras/nova')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('ComprasForm')->has('weeks', 5)->where('list', null));

        $this->actingAs($athlete)
            ->post('/objetivos/alimentacao/compras', ['week' => 3, 'items' => ['Patinho']])
            ->assertRedirect('/objetivos/alimentacao/compras');

        $athlete = $athlete->fresh();

        $this->actingAs($athlete)
            ->get('/objetivos/alimentacao/compras/nova')
            ->assertInertia(fn (Assert $page) => $page->has('weeks', 4)->where('weeks.2.week', 4));

        $this->actingAs($athlete)
            ->post('/objetivos/alimentacao/compras', ['week' => 3, 'items' => ['Lagarto']])
            ->assertSessionHasErrors('week');

        $this->assertSame(['Patinho'], $athlete->fresh()->athleteProfile->shopping_lists['3']['items']);

        $this->actingAs($athlete)
            ->get('/objetivos/alimentacao/compras/3/editar')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('weeks', 1)->where('list.items', ['Patinho']));

        $this->actingAs($athlete)->get('/objetivos/alimentacao/compras/5/editar')->assertNotFound();
    }

    public function test_an_athlete_saves_a_shopping_list_for_one_week(): void
    {
        $athlete = User::factory()->create();

        $this->get('/objetivos/alimentacao/compras')
            ->assertRedirect(route('login'));

        $this->actingAs($athlete)
            ->put('/objetivos/alimentacao/compras', [
                'week' => 2,
                'items' => ['Patinho', 'Ovos de galinha'],
                'extras' => ['Queijo da fazenda'],
            ])
            ->assertRedirect('/objetivos/alimentacao/compras');

        $this->actingAs($athlete)
            ->get('/objetivos/alimentacao/compras')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('canSave', true)
                ->where('lists.0.week', 2)
                ->where('lists.0.period', '16/11 a 22/11')
                ->where('lists.0.items', ['Patinho', 'Ovos de galinha'])
                ->where('lists.0.extras', ['Queijo da fazenda']));

        $this->actingAs($athlete)
            ->put('/objetivos/alimentacao/compras', [
                'week' => 2,
                'items' => [],
                'extras' => [],
            ])
            ->assertRedirect('/objetivos/alimentacao/compras');

        $this->assertNull($athlete->fresh()->athleteProfile->shopping_lists);

        $this->actingAs(User::factory()->ofRole(UserRole::Coach)->create())
            ->put('/objetivos/alimentacao/compras', [
                'week' => 1,
                'items' => ['Patinho'],
            ])
            ->assertForbidden();
    }

    public function test_a_goal_opens_inside_the_logged_area(): void
    {
        $this->get('/meus-objetivos/treino')->assertRedirect('/login');

        $this->actingAs(User::factory()->create())
            ->get('/meus-objetivos/treino')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Objetivo')->where('goal', 'treino'));

        $this->actingAs(User::factory()->create())
            ->get('/meus-objetivos/nada')
            ->assertNotFound();
    }

    public function test_each_evolution_submenu_has_its_own_page(): void
    {
        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)->get('/atleta/evolucao')->assertRedirect('/atleta/evolucao/dias');

        foreach (['dias', 'conquistas', 'resumo'] as $tab) {
            $this->actingAs($athlete)
                ->get("/atleta/evolucao/{$tab}")
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('dashboard/Evolucao')
                    ->where('tab', $tab)
                    ->where('skillCatalog.0.title', 'Mestre-cuca da vida real')
                    ->where('skillCatalog.0.items.0', 'Aprender a fazer 5 pratos saudáveis que nunca fez.'));
        }

        $this->actingAs($athlete)
            ->put('/atleta/dias', ['days' => [['day' => 2, 'details' => ['workouts' => [['type' => 'Caminhada', 'minutes' => 20]], 'skill' => 'Aprender a fazer 5 pratos saudáveis que nunca fez.']]]])
            ->assertRedirect('/atleta/evolucao/dias');

        $this->assertDatabaseHas('checkins', ['user_id' => $athlete->id, 'day' => 2, 'skill' => true]);
        $this->assertDatabaseCount('skills', 22);

        $this->actingAs($athlete)->get('/atleta/evolucao/outra')->assertNotFound();
        $this->actingAs($athlete)->get('/atleta/evolucao/metas')->assertRedirect('/atleta/ponto-de-partida/metas');
    }

    public function test_day_details_reach_the_goals_or_count_manually(): void
    {
        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)
            ->put('/atleta/dias', ['days' => [
                [
                    'day' => 1,
                    'checked_in' => true,
                    'details' => [
                        'workouts' => [['type' => 'Corrida', 'minutes' => 30], ['type' => 'Musculação', 'minutes' => 15]],
                        'water_ml' => 2500,
                        'sleep_hours' => 7.5,
                        'food' => ['no_alcohol' => true, 'no_fast_food' => true, 'no_sweet' => true],
                        'skill' => 'Cozinhar um prato novo',
                    ],
                ],
                [
                    'day' => 2,
                    'checked_in' => true,
                    'details' => [
                        'workouts' => [['type' => 'Caminhada', 'minutes' => 20]],
                        'water_ml' => 1000,
                        'sleep_hours' => 5,
                        'food' => ['no_alcohol' => true],
                        'manual' => ['movement' => true],
                    ],
                ],
                [
                    'day' => 3,
                    'checked_in' => false,
                    'details' => [
                        'workouts' => [['type' => 'Caminhada', 'minutes' => 20]],
                        'water_ml' => 1000,
                        'sleep_hours' => 5,
                    ],
                ],
            ]])
            ->assertRedirect('/atleta/evolucao/dias');

        $full = $athlete->checkins()->where('day', 1)->first();
        $this->assertTrue($full->movement && $full->water && $full->sleep && $full->strength && $full->skill);
        $this->assertSame(10, \App\Support\ChallengeSheet::dayPoints($full));
        $this->assertSame(45, array_sum(array_column($full->details['workouts'], 'minutes')));

        $this->get('/atleta/evolucao/dias')->assertInertia(fn (Assert $page) => $page
            ->where('days.0.details.workouts.0.type', 'Corrida')
            ->where('days.0.details.workouts.1.type', 'Musculação'));

        $manual = $athlete->checkins()->where('day', 2)->first();
        $this->assertTrue($manual->movement);
        $this->assertFalse($manual->water || $manual->sleep || $manual->strength || $manual->skill);
        $this->assertSame(4, \App\Support\ChallengeSheet::dayPoints($manual));

        $short = $athlete->checkins()->where('day', 3)->first();
        $this->assertTrue($short->checked_in);
        $this->assertSame(1, \App\Support\ChallengeSheet::dayPoints($short));
    }

    public function test_the_goal_comes_from_the_profile_and_legacy_days_keep_their_points(): void
    {
        $athlete = User::factory()->create();
        \App\Models\AthleteProfile::query()->create(['user_id' => $athlete->id, 'welcomed_at' => now(), 'goals_set_at' => now(), 'movement_level' => 'iniciante', 'water_liters' => 2]);

        $this->actingAs($athlete)
            ->put('/atleta/dias', ['days' => [[
                'day' => 1,
                'checked_in' => true,
                'details' => ['workouts' => [['type' => 'Yoga', 'minutes' => 20]], 'water_ml' => 2000],
            ]]])
            ->assertRedirect();

        $day = $athlete->checkins()->where('day', 1)->first();
        $this->assertTrue($day->movement);
        $this->assertTrue($day->water);

        // Dia salvo só com os checks (antes dos detalhes): continua pontuando e aparece como manual.
        $athlete->checkins()->create(['day' => 2, 'checked_in' => true, 'movement' => true, 'water' => true]);

        $this->actingAs($athlete->fresh())
            ->get('/atleta/evolucao/dias')
            ->assertInertia(fn (Assert $page) => $page
                ->where('goals.movement_minutes', 20)
                ->where('goals.water_ml', 2000)
                ->where('days.1.details.manual.movement', true)
                ->where('days.1.details.manual.water', true)
                ->where('days.1.points', 5));
    }

    public function test_the_water_goal_minimum_comes_from_the_weight(): void
    {
        $athlete = User::factory()->create();
        \App\Models\AthleteProfile::query()->create(['user_id' => $athlete->id, 'weight' => 80, 'welcomed_at' => now()]);

        $this->actingAs($athlete)
            ->get('/atleta/ponto-de-partida/metas')
            ->assertInertia(fn (Assert $page) => $page->where('waterMinimum', 2.8)->where('profile.water_liters', ''));

        $this->assertSame(2800, \App\Support\DayLog::goals($athlete->fresh()->athleteProfile)['water_ml']);
    }

    public function test_the_evolution_stays_locked_until_first_day_and_goals_are_filled(): void
    {
        $athlete = User::factory()->create();
        $day = ['days' => [['day' => 1, 'checked_in' => true]]];

        $this->actingAs($athlete)->get('/atleta/evolucao/dias')->assertRedirect('/atleta/ponto-de-partida/primeiro-dia');
        $this->put('/atleta/dias', $day)->assertRedirect('/atleta/ponto-de-partida/primeiro-dia');
        $this->assertDatabaseCount('checkins', 0);

        \App\Models\AthleteProfile::query()->create(['user_id' => $athlete->id, 'welcomed_at' => now()]);

        $this->actingAs($athlete->fresh())->get('/atleta/evolucao/conquistas')->assertRedirect('/atleta/ponto-de-partida/metas');
        $this->put('/atleta/dias', $day)->assertRedirect('/atleta/ponto-de-partida/metas');
        $this->assertDatabaseCount('checkins', 0);

        $athlete->athleteProfile()->update(['goals_set_at' => now()]);

        $this->actingAs($athlete->fresh())->get('/atleta/evolucao/dias')->assertOk();
    }
    public function test_an_athlete_adds_replaces_and_removes_the_workout_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)
            ->post('/atleta/dias/2/foto-treino', ['photo' => \Illuminate\Http\UploadedFile::fake()->image('treino.jpg')])
            ->assertRedirect();

        $first = $athlete->checkins()->where('day', 2)->firstOrFail()->workout_photo;
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($first);

        $this->post('/atleta/dias/2/foto-treino', ['photo' => \Illuminate\Http\UploadedFile::fake()->image('outro.png')]);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($first);

        $this->get('/atleta/evolucao/dias')
            ->assertInertia(fn (Assert $page) => $page->where('days.1.saved', false)->where('days.1.workout_photo', fn ($url) => str_starts_with($url, '/storage/workouts/')));

        $this->post('/atleta/dias/2/foto-treino', ['photo' => \Illuminate\Http\UploadedFile::fake()->create('x.pdf', 10)])->assertSessionHasErrors('photo');

        $this->delete('/atleta/dias/2/foto-treino')->assertRedirect();
        $this->assertNull($athlete->checkins()->where('day', 2)->firstOrFail()->workout_photo);

        $this->actingAs(User::factory()->create())
            ->post('/atleta/dias/2/foto-treino', ['photo' => \Illuminate\Http\UploadedFile::fake()->image('a.jpg')])
            ->assertRedirect('/atleta/ponto-de-partida/primeiro-dia');
    }
    public function test_an_athlete_posts_a_meal_photo_and_the_nutritionist_leaves_praise(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)
            ->post('/atleta/dias/2/foto-refeicao', ['photo' => \Illuminate\Http\UploadedFile::fake()->image('prato.jpg')])
            ->assertRedirect();

        $first = $athlete->checkins()->where('day', 2)->firstOrFail()->meal_photo;
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($first);

        $this->post('/atleta/dias/2/foto-refeicao', ['photo' => \Illuminate\Http\UploadedFile::fake()->image('outro.png')]);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($first);

        $this->get('/atleta/evolucao/dias')
            ->assertInertia(fn (Assert $page) => $page->where('days.1.meal_photo', fn ($url) => str_starts_with($url, '/storage/meals/')));

        $this->post('/atleta/dias/2/foto-refeicao', ['photo' => \Illuminate\Http\UploadedFile::fake()->create('x.pdf', 10)])->assertSessionHasErrors('photo');

        $nutri = User::factory()->ofRole(UserRole::Nutricionista)->create();

        $this->actingAs($nutri)
            ->get("/equipe/atletas/{$athlete->id}/evolucao")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('canComment', true)
                ->where('canEdit', false)
                ->where('athleteId', $athlete->id)
                ->where('days.1.meal_photo', fn ($url) => str_starts_with($url, '/storage/meals/')));

        $this->put("/equipe/atletas/{$athlete->id}/dias/2/comentario-refeicao", ['comment' => 'Prato colorido, mandou bem!'])
            ->assertRedirect();

        $this->assertDatabaseHas('checkins', [
            'user_id' => $athlete->id,
            'day' => 2,
            'meal_comment' => 'Prato colorido, mandou bem!',
        ]);

        $athlete->unsetRelation('checkins');

        $this->actingAs($athlete)
            ->get('/atleta/evolucao/dias')
            ->assertInertia(fn (Assert $page) => $page->where('days.1.meal_comment', 'Prato colorido, mandou bem!'));

        $this->put("/equipe/atletas/{$athlete->id}/dias/2/comentario-refeicao", ['comment' => 'Não pode'])
            ->assertForbidden();

        $this->delete('/atleta/dias/2/foto-refeicao')->assertRedirect();
        $this->assertNull($athlete->checkins()->where('day', 2)->firstOrFail()->meal_photo);

        $this->actingAs(User::factory()->ofRole(UserRole::Coach)->create())
            ->put("/equipe/atletas/{$athlete->id}/dias/2/comentario-refeicao", ['comment' => 'Coach'])
            ->assertForbidden();
    }
    public function test_a_single_day_can_be_saved_alone_and_then_comes_back_locked(): void
    {
        $athlete = $this->startedAthlete();

        $this->actingAs($athlete)
            ->put('/atleta/dias', ['days' => [['day' => 1, 'details' => ['water_ml' => 500]]]])
            ->assertSessionHasErrors('days.0.details.workouts');

        $this->actingAs($athlete)
            ->put('/atleta/dias', ['days' => [['day' => 3, 'details' => ['workouts' => [['type' => 'Caminhada', 'minutes' => 20]], 'water_ml' => 500]]]])
            ->assertRedirect('/atleta/evolucao/dias');

        $this->assertDatabaseCount('checkins', 1);

        $this->get('/atleta/evolucao/dias')->assertInertia(fn (Assert $page) => $page
            ->where('days.0.saved', false)
            ->where('days.2.saved', true)
            ->where('days.3.saved', false)
            ->where('days.2.checked_in', true)
            ->where('days.2.details.water_ml', 500));
    }
    private function startedAthlete(): User
    {
        $athlete = User::factory()->create();
        \App\Models\AthleteProfile::query()->create(['user_id' => $athlete->id, 'welcomed_at' => now(), 'goals_set_at' => now()]);

        return $athlete;
    }
}
