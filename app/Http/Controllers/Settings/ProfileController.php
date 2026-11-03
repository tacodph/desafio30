<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\ChallengePayment;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $paymentInfo = null;

        if ($user->isAthlete()) {
            $latestPayment = $user->payments()->latest('id')->first();
            $status = 'novo';

            if ($user->isCourtesy()) {
                $status = 'cortesia';
            } elseif ($user->hasPaidChallenge()) {
                $status = 'confirmado';
            } elseif ($latestPayment?->status === ChallengePayment::REFUNDED) {
                $status = 'reembolsado';
            } elseif ($latestPayment) {
                $status = $latestPayment->status === ChallengePayment::PENDING
                    && $latestPayment->asaas_payment_id
                    && $latestPayment->due_date?->gte(today())
                        ? 'pendente'
                        : 'vencido';
            }

            $paymentInfo = [
                'status' => $status,
                'value' => $user->isCourtesy() ? 0 : ($latestPayment?->value ?? config('desafio.price')),
                'dueDate' => $user->isCourtesy() ? null : $latestPayment?->due_date?->format('d/m/Y'),
            ];
        }

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'paymentInfo' => $paymentInfo,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->safe()->except('avatar'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $user->avatar));
            }

            $user->avatar = '/storage/'.$request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
