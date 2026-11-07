<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\WorkoutType;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WorkoutTypeController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()->userType?->name === UserRole::Administrador->value, 403);

        return Inertia::render('dashboard/WorkoutTypes', [
            'workoutTypes' => WorkoutType::query()->orderBy('id')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->userType?->name === UserRole::Administrador->value, 403);

        $data = $request->validate(['name' => ['required', 'string', 'max:40']]);
        $name = Str::squish($data['name']);

        if ($name === '' || WorkoutType::query()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
            throw ValidationException::withMessages(['name' => 'Informe uma modalidade que ainda não esteja cadastrada.']);
        }

        try {
            WorkoutType::query()->create(['name' => $name]);
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['name' => 'Essa modalidade já está cadastrada.']);
        }

        return to_route('admin.workout-types')->with('workout_type_saved', true);
    }
}
