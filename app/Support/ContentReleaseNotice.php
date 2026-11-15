<?php

namespace App\Support;

use Illuminate\Http\Request;

class ContentReleaseNotice
{
    public static function pages(Request $request): array
    {
        $user = $request->user();
        if (! $user?->isAthlete() || ! $user->hasChallengeAccess()
            || $user->content_release_notice_shown_at
            || ! $request->session()->get('content_release_notice_pending')
            || ! ChallengeAccess::sevenDayContentIsReleased()) {
            return [];
        }

        $pages = [];
        if ($user->canViewConsultationMeals()) {
            $pages[] = ['title' => 'Sugestões de refeições reais - Jantar', 'href' => '/alimentacao/refeicoes-reais-em-consultorio'];
        }
        if (app(ChallengeAccess::class)->pageOpen('nutri')) {
            $pages[] = ['title' => 'Receitas no Cantinho da Nutri', 'href' => '/cantinho-da-nutri'];
        }

        return $pages;
    }
}
