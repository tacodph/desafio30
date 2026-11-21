<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PrivateCommentController extends Controller
{
    private function context(Request $request): string
    {
        $user = $request->user();
        abort_unless(in_array($user->userType?->name, ['atleta', 'nutricionista', 'administrador'], true), 403);
        $data = $request->validate(['context' => ['required', 'string', 'max:100']]);
        $context = $data['context'];

        if (preg_match('/^meal:(\d+):(\d+)$/', $context, $matches)) {
            $athlete = User::findOrFail($matches[1]);
            abort_unless($athlete->isAthlete(), 404);
            abort_if($user->isAthlete() && $user->id !== $athlete->id, 403);
            $athlete->checkins()->where('day', $matches[2])->whereNotNull('meal_comment')->where('meal_comment', '!=', '')->firstOrFail();
        } else {
            abort_unless(in_array($context, ['video:jantar', 'video:recado-nutri'], true), 404);
            abort_if($context === 'video:jantar' && $user->isAthlete() && ! app(\App\Support\ChallengeAccess::class)->pageOpen('alimentacao'), 403);
        }

        return $context;
    }

    public function index(Request $request)
    {
        $context = $this->context($request);
        $query = DB::table('private_comments')->join('users', 'users.id', '=', 'private_comments.user_id')
            ->where('context', $context)->where('private_comments.challenge_id', \App\Support\ChallengeContext::challenge()->id);
        if ($request->user()->isAthlete()) {
            $query->where('private_comments.user_id', $request->user()->id);
        }

        return response()->json(['comments' => $query->orderByDesc('private_comments.id')->paginate(20, [
            'private_comments.id', 'private_comments.body', 'private_comments.created_at', 'users.name',
        ])]);
    }

    public function store(Request $request)
    {
        $context = $this->context($request);
        abort_unless($request->user()->isAthlete(), 403);
        $data = $request->validate(['body' => ['required', 'string', 'max:2000']]);
        $body = trim($data['body']);
        abort_if($body === '', 422, 'Escreva um comentário.');
        DB::table('private_comments')->insert([
            'user_id' => $request->user()->id, 'context' => $context,
            'challenge_id' => \App\Support\ChallengeContext::challenge()->id,
            'body' => $body, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return response()->json(['saved' => true], 201);
    }
}
