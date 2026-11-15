<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('mail.manager', fn ($manager, $app) => new \App\Mail\AuditedMailManager($app));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function (Login $event) {
            DB::table('login_logs')->insert([
                'user_id' => $event->user->getAuthIdentifier(),
                'logged_in_at' => now(),
            ]);

            if (! $event->user->isAthlete() || ! request()->hasSession()) {
                return;
            }

            request()->session()->put('meal_comment_notice_ids', $event->user->checkins()
                ->whereNotNull('meal_comment')
                ->where('meal_comment', '!=', '')
                ->whereNull('meal_comment_notice_shown_at')
                ->orderBy('day')
                ->pluck('id')->all());
        });
    }
}
