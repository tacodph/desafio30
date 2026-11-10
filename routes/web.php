<?php

use App\Http\Controllers\AthleteProgressController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ChallengeAccessController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\SupportController;
use App\Models\Skill;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('suporte', [SupportController::class, 'show'])->name('suporte');
Route::get('ajuda/duvidas-frequentes', [\App\Http\Controllers\FaqController::class, 'index'])->name('faq');
Route::post('suporte', [SupportController::class, 'store'])->middleware('throttle:3,1')->name('suporte.store');

Route::get('participar', function () {
    return Inertia::render('Participar', [
        'price' => config('desafio.price'),
    ]);
})->name('participar');

Route::redirect('objetivos', '/objetivos/alimentacao')->name('objetivos');
Route::redirect('objetivos/plus', '/objetivo/luxo');
Route::redirect('objetivos/oracao', '/objetivo/luxo');
Route::redirect('objetivos/corpo', '/objetivos/alimentacao');
Route::redirect('objetivos/flexoes', '/objetivos/alimentacao');

Route::get('objetivos/alimentacao/compras', [AthleteProgressController::class, 'shopping'])->middleware('auth')->name('alimentacao.compras');

Route::get('objetivo/luxo', function () {
    return Inertia::render('Objetivos', [
        'goal' => 'plus',
        'skillCatalog' => Skill::catalog(),
    ]);
})->name('objetivo.luxo');

Route::get('objetivos/{goal}', function (string $goal) {
    $keys = ['alimentacao', 'treino', 'hidratacao', 'habilidade', 'plus'];

    if (! in_array($goal, $keys, true)) {
        abort(404);
    }

    return Inertia::render('Objetivos', [
        'goal' => $goal,
        'skillCatalog' => Skill::catalog(),
    ]);
})->name('objetivos.show');

Route::middleware('auth')->group(function () {
    Route::get('pagamento', [PaymentController::class, 'show'])->name('pagamento.show');
    Route::post('pagamento', [PaymentController::class, 'store'])->middleware('throttle:6,1')->name('pagamento.store');
    Route::get('pagamento/pagar', [PaymentController::class, 'pay'])->name('pagamento.pagar');
    Route::get('pagamento/retorno', [PaymentController::class, 'callback'])->name('pagamento.retorno');
});

Route::post('webhooks/asaas', WebhookController::class)->name('webhooks.asaas');

Route::middleware(['auth', 'verified', 'challenge.paid'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('cantinho-da-nutri', [AthleteProgressController::class, 'nutritionMaterials'])->middleware('challenge.feature:nutri')->name('nutri.index');
    Route::get('cantinho-da-nutri/a-nutri-indica', fn () => Inertia::render('dashboard/NutriIndica'))->middleware('challenge.feature:nutri')->name('nutri.recommendations');
    Route::get('cantinho-da-nutri/monitoramento', [AthleteProgressController::class, 'monitoring'])->middleware('challenge.feature:nutri')->name('nutri.monitoring');
    Route::get('cantinho-da-nutri/monitoramento/imprimir', fn () => Inertia::render('dashboard/MonitoramentoManual', [
        'startDate' => \App\Support\ChallengeSheet::START,
        'totalDays' => \App\Support\ChallengeSheet::TOTAL_DAYS,
    ]))->middleware('challenge.feature:nutri')->name('nutri.monitoring.print');
    Route::redirect('alimentacao/como-combinar', '/cantinho-da-nutri')->name('alimentacao.combinacoes');
    Route::get('alimentacao/monte-seu-prato', fn () => Inertia::render('MonteSeuPrato'))->middleware('challenge.feature:alimentacao')->name('alimentacao.prato');
    Route::get('alimentacao/escolhendo-melhor-fora-de-casa', fn () => Inertia::render('EscolhendoMelhorForaDeCasa'))->middleware('challenge.feature:alimentacao')->name('alimentacao.fora-de-casa');
    Route::get('alimentacao/lidando-com-o-fim-de-semana', fn () => Inertia::render('LidandoComOFimDeSemana'))->middleware('challenge.feature:alimentacao')->name('alimentacao.fim-de-semana');
    Route::get('alimentacao/recado-da-nutri', fn () => Inertia::render('NutriMessage'))->middleware('challenge.feature:alimentacao')->name('alimentacao.recado-da-nutri');
    Route::get('ajuda/como-funciona-a-alimentacao', fn () => Inertia::render('FoodGuide'))->name('ajuda.alimentacao');
    Route::get('pontuacao', [DashboardController::class, 'scoring'])->name('pontuacao');
    Route::put('pontuacao', [DashboardController::class, 'updateScoring'])->name('pontuacao.update');
    Route::get('meus-objetivos/{goal}', function (string $goal) {
        abort_unless(in_array($goal, ['alimentacao', 'treino', 'hidratacao', 'habilidade', 'plus'], true), 404);

        return Inertia::render('Objetivo', ['goal' => $goal, 'skillCatalog' => Skill::catalog()]);
    })->name('meus-objetivos.show');
    Route::get('admin/boas-vindas', [DashboardController::class, 'welcomePreview'])->name('admin.welcome.preview');
    Route::get('admin/mensagens/boas-vindas-video', [DashboardController::class, 'releaseWelcomePreview'])->name('admin.release-welcome.preview');
    Route::get('admin/mensagens/emails', [\App\Http\Controllers\EmailLogController::class, 'index'])->name('admin.email-logs');
    Route::get('admin/liberacoes', [ChallengeAccessController::class, 'show'])->name('admin.challenge-access');
    Route::patch('admin/liberacoes/atleta', [ChallengeAccessController::class, 'updateAthlete'])->middleware('throttle:6,1')->name('admin.challenge-access.athlete');
    Route::put('admin/liberacoes', [ChallengeAccessController::class, 'update'])->middleware('throttle:6,1')->name('admin.challenge-access.update');
    Route::get('admin/usuarios', [DashboardController::class, 'users'])->name('admin.users');
    Route::get('admin/pagamentos/lembretes', [\App\Http\Controllers\PaymentReminderController::class, 'show'])->name('admin.payment-reminders');
    Route::post('admin/pagamentos/lembretes', [\App\Http\Controllers\PaymentReminderController::class, 'store'])->middleware('throttle:6,1')->name('admin.payment-reminders.store');
    Route::get('admin/pagamentos', [DashboardController::class, 'payments'])->name('admin.payments');
    Route::get('admin/usuarios/novo', [DashboardController::class, 'createUser'])->name('admin.users.create');
    Route::patch('admin/usuarios/{athlete}/cortesia', [DashboardController::class, 'updateCourtesy'])->name('admin.users.courtesy');
    Route::post('admin/usuarios', [DashboardController::class, 'storeUser'])->name('admin.users.store');
    Route::get('admin/treinos', [\App\Http\Controllers\WorkoutTypeController::class, 'index'])->name('admin.workout-types');
    Route::post('admin/treinos', [\App\Http\Controllers\WorkoutTypeController::class, 'store'])->name('admin.workout-types.store');
    Route::get('admin/alimentos', [DashboardController::class, 'foods'])->name('admin.foods');
    Route::post('admin/alimentos', [DashboardController::class, 'storeFood'])->name('admin.foods.store');
    Route::patch('admin/alimentos/{food}', [DashboardController::class, 'toggleFood'])->name('admin.foods.toggle');
    Route::delete('admin/alimentos/{food}', [DashboardController::class, 'destroyFood'])->name('admin.foods.destroy');
    Route::get('equipe/atletas', [DashboardController::class, 'athletes'])->name('equipe.athletes');
    Route::get('equipe/check-ins', [DashboardController::class, 'athleteCheckins'])->name('equipe.checkins');
    Route::get('equipe/conquistas', [DashboardController::class, 'athleteAchievements'])->name('equipe.achievements');
    Route::get('equipe/alimentacao', [DashboardController::class, 'athleteMeals'])->name('equipe.meals');
    Route::get('equipe/alimentacao/fotos/{checkin}/{photo}', [DashboardController::class, 'athleteMealPhoto'])->whereNumber('checkin')->name('equipe.meals.photo');
    Route::get('equipe/atletas/{athlete}/evolucao', [AthleteProgressController::class, 'showAthlete'])->name('equipe.athlete.progress');
    Route::get('atleta/ponto-de-partida/primeiro-dia', [AthleteProgressController::class, 'firstDay'])->name('athlete.start.first-day');
    Route::post('atleta/ponto-de-partida/primeiro-dia', [AthleteProgressController::class, 'storeFirstDay'])->name('athlete.start.first-day.store');
    Route::get('atleta/ponto-de-partida/como-tirar-medidas', fn () => Inertia::render('dashboard/ComoTirarMedidas'))->name('athlete.start.measures-guide');
    Route::get('atleta/ponto-de-partida/metas', [AthleteProgressController::class, 'goals'])->name('athlete.start.goals');
    Route::get('atleta/boas-vindas', [AthleteProgressController::class, 'welcome'])->name('athlete.welcome');
    Route::get('atleta/boas-vindas/video', [AthleteProgressController::class, 'welcomeVideo'])->name('athlete.welcome.video');
    Route::get('atleta/boas-vindas/objetivos', [AthleteProgressController::class, 'welcomeObjectives'])->name('athlete.welcome.objectives');
    Route::get('atleta/boas-vindas/plataforma', [AthleteProgressController::class, 'welcomePlatform'])->name('athlete.welcome.platform');
    Route::get('atleta/boas-vindas/aproveitar-melhor', [AthleteProgressController::class, 'welcomeTips'])->name('athlete.welcome.tips');
    Route::get('atleta/boas-vindas/tutoriais/tela-inicial', [AthleteProgressController::class, 'homeScreenTutorial'])->name('athlete.tutorial.home-screen');
    Route::post('atleta/boas-vindas', [AthleteProgressController::class, 'acknowledgeWelcome'])->name('athlete.welcome.acknowledge');
    Route::post('atleta/boas-vindas/liberacao', [AthleteProgressController::class, 'acknowledgeReleaseWelcome'])->name('athlete.release-welcome.acknowledge');
    Route::redirect('atleta/evolucao/metas', '/atleta/ponto-de-partida/metas');
    Route::redirect('atleta/evolucao', '/atleta/evolucao/dias')->name('athlete.progress');
    Route::get('atleta/evolucao/{tab}', [AthleteProgressController::class, 'show'])
        ->whereIn('tab', ['dias', 'conquistas', 'resumo'])
        ->name('athlete.progress.tab');
    Route::put('atleta/metas', [AthleteProgressController::class, 'updateGoals'])->name('athlete.goals.update');
    Route::put('atleta/habilidades', [AthleteProgressController::class, 'updateSkills'])->name('athlete.skills.update');
    Route::get('objetivos/alimentacao/compras/nova', [AthleteProgressController::class, 'createShopping'])->name('alimentacao.compras.create');
    Route::get('objetivos/alimentacao/compras/{week}/editar', [AthleteProgressController::class, 'editShopping'])->whereNumber('week')->name('alimentacao.compras.edit');
    Route::post('objetivos/alimentacao/compras', [AthleteProgressController::class, 'storeShopping'])->name('alimentacao.compras.store');
    Route::put('objetivos/alimentacao/compras', [AthleteProgressController::class, 'updateShopping'])->name('alimentacao.compras.update');
    Route::patch('atleta/dias/{day}/objetivos/{goal}', [AthleteProgressController::class, 'updateDayGoal'])
        ->whereNumber('day')->whereIn('goal', ['movement', 'water', 'sleep', 'strength', 'skill', 'plus', 'notes'])
        ->name('athlete.days.goal.update');
    Route::put('atleta/dias', [AthleteProgressController::class, 'updateDays'])->name('athlete.days.update');
    Route::post('atleta/dias/{day}/foto-treino', [AthleteProgressController::class, 'storeWorkoutPhoto'])->whereNumber('day')->name('athlete.days.photo.store');
    Route::delete('atleta/dias/{day}/foto-treino', [AthleteProgressController::class, 'destroyWorkoutPhoto'])->whereNumber('day')->name('athlete.days.photo.destroy');
    Route::post('atleta/dias/{day}/foto-refeicao', [AthleteProgressController::class, 'storeMealPhoto'])->whereNumber('day')->name('athlete.days.meal.store');
    Route::delete('atleta/dias/{day}/foto-refeicao/{photo?}', [AthleteProgressController::class, 'destroyMealPhoto'])->whereNumber('day')->name('athlete.days.meal.destroy');
    Route::put('equipe/atletas/{athlete}/dias/{day}/comentario-refeicao', [AthleteProgressController::class, 'commentMeal'])->whereNumber('day')->name('equipe.athlete.meal.comment');
    Route::post('atleta/conquistas', [AthleteProgressController::class, 'storeAchievement'])->name('athlete.achievements.store');
    Route::put('atleta/conquistas/{achievement}', [AthleteProgressController::class, 'updateAchievement'])->name('athlete.achievements.update');
    Route::put('atleta/conquistas/{achievement}/comentario', [AthleteProgressController::class, 'commentAchievement'])->name('athlete.achievements.comment');
    Route::delete('atleta/conquistas/{achievement}', [AthleteProgressController::class, 'destroyAchievement'])->name('athlete.achievements.destroy');
});

require __DIR__.'/settings.php';
Route::middleware('auth')->group(function () {
    Route::get('admin/duvidas-frequentes', [\App\Http\Controllers\FaqController::class, 'admin'])->name('admin.faqs');
    Route::post('admin/duvidas-frequentes', [\App\Http\Controllers\FaqController::class, 'store'])->middleware('throttle:30,1')->name('admin.faqs.store');
    Route::put('admin/duvidas-frequentes/{faq}', [\App\Http\Controllers\FaqController::class, 'update'])->middleware('throttle:30,1')->name('admin.faqs.update');
});
require __DIR__.'/auth.php';
