<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserType;
use App\Support\ChallengeAccess;
use App\Support\ChallengeSheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class ChallengeAccessController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()->userType?->name === UserRole::Administrador->value, 403);
    }

    public function show(Request $request): Response
    {
        $this->authorizeAdmin($request);
        $access = new ChallengeAccess;

        return Inertia::render('dashboard/ChallengeAccess', [
            'features' => ChallengeAccess::FEATURES,
            'athletes' => Schema::hasColumn('users', 'challenge_early_access')
                ? User::query()->where('user_type_id', UserType::idFor(UserRole::Atleta))
                    ->orderBy('name')->orderBy('id')->get(['id', 'name', 'email', 'challenge_early_access'])
                : [],
            'athleteSaved' => (bool) $request->session()->get('athlete_access_saved', false),
            'modes' => $access->modes(),
            'releaseDate' => $access->releaseDate()->format('d/m/Y'),
            'startDate' => ChallengeSheet::dateForDay(1),
            'endDate' => ChallengeSheet::dateForDay(ChallengeSheet::TOTAL_DAYS),
            'environment' => app()->environment(),
            'ready' => Schema::hasTable('challenge_access_settings'),
            'saved' => (bool) $request->session()->get('access_saved', false),
        ]);
    }

    public function updateAthlete(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);

        $data = $request->validateWithBag('athleteAccess', [
            'athlete_id' => ['required', 'integer'],
            'enabled' => ['required', 'boolean'],
            'password' => ['required', 'current_password:web'],
        ]);

        $athlete = User::query()
            ->where('user_type_id', UserType::idFor(UserRole::Atleta))
            ->findOrFail($data['athlete_id']);
        $athlete->forceFill(['challenge_early_access' => $data['enabled']])->save();

        return to_route('admin.challenge-access')->with('athlete_access_saved', true);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $rules = ['password' => ['required', 'current_password:web'], 'modes' => ['required', 'array:'.implode(',', array_keys(ChallengeAccess::FEATURES))]];
        foreach (array_keys(ChallengeAccess::FEATURES) as $key) {
            $rules['modes.'.$key] = ['required', 'in:'.implode(',', ChallengeAccess::MODES)];
        }
        $data = $request->validate($rules, [
            'password.current_password' => 'A senha do administrador está incorreta.',
        ]);
        abort_unless(Schema::hasTable('challenge_access_settings'), 503, 'Execute as migrações para ativar os controles.');

        DB::transaction(function () use ($data, $request) {
            foreach (array_keys(ChallengeAccess::FEATURES) as $feature) {
                DB::table('challenge_access_settings')->updateOrInsert(['feature' => $feature], [
                    'mode' => $data['modes'][$feature],
                    'updated_by' => $request->user()->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return to_route('admin.challenge-access')->with('access_saved', true);
    }
}
