<?php

namespace App\Http\Controllers;

use App\Support\ContentReleaseNotice;
use Illuminate\Http\Request;

class ContentReleaseNoticeController extends Controller
{
    public function store(Request $request)
    {
        abort_unless($request->user()->isAthlete(), 403);
        $pages = ContentReleaseNotice::pages($request);
        abort_if($pages === [], 404);
        $shown = $request->user()->newQuery()->whereKey($request->user()->id)
            ->whereNull('content_release_notice_shown_at')
            ->update(['content_release_notice_shown_at' => now()]);
        $request->session()->forget('content_release_notice_pending');

        return response()->json(['show' => $shown === 1, 'pages' => $shown === 1 ? $pages : []]);
    }
}
