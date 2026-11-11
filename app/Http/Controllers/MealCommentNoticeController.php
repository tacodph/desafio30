<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MealCommentNoticeController extends Controller
{
    public function store(Request $request, int $checkin): JsonResponse
    {
        abort_unless($request->user()->isAthlete(), 403);
        abort_unless(in_array($checkin, $request->session()->get('meal_comment_notice_ids', []), true), 404);

        $shown = $request->user()->checkins()
            ->whereKey($checkin)
            ->whereNotNull('meal_comment')
            ->where('meal_comment', '!=', '')
            ->whereNull('meal_comment_notice_shown_at')
            ->update(['meal_comment_notice_shown_at' => now()]);

        return response()->json(['show' => $shown === 1]);
    }
}
