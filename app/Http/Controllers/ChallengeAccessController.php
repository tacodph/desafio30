<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
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
            'modes' => $access->modes(),
            'releaseDate' => $access->releaseDate()->format('d/m/Y'),
            'startDate' => ChallengeSheet::dateForDay(1),
            'endDate' => ChallengeSheet::dateForDay(ChallengeSheet::TOTAL_DAYS),
            'environment' => app()->environment(),
            'ready' => Schema::hasTable('challenge_access_settings'),
            'saved' => (bool) $request->session()->get('access_saved', false),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $rules = ['password' => ['required', 'current_password:web'], 'modes' => ['required', 'array:'.implode(',', array_keys(ChallengeAccess::FEATURES))]];
        foreach (array_keys(ChallengeAccess::FEATURES) as $key) {
            $rules['modes.'.$key] = ['required', 'in:calendar,open,closed'];
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
