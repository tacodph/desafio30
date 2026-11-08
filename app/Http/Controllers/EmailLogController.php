<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailLogController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->userType?->name === UserRole::Administrador->value, 403);
        $filters = $request->validate([
            'status' => ['nullable', 'in:queued,sending,sent,failed,logged,processed,skipped'],
            'category' => ['nullable', 'in:payment_reminder,support,notification,platform'],
            'search' => ['nullable', 'string', 'max:150'],
        ]);
        $logs = EmailLog::query()
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category))
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(recipient_email) LIKE ?', ['%'.mb_strtolower($search).'%'])
                    ->orWhereRaw('LOWER(subject) LIKE ?', ['%'.mb_strtolower($search).'%']);
            }))
            ->latest('id')->paginate(20, ['id', 'actor_id', 'category', 'recipient_email', 'recipients', 'sender', 'reply_to', 'subject', 'body_text', 'status', 'mailer', 'attempts', 'error', 'message_id', 'attempted_at', 'sent_at', 'created_at'])
            ->withQueryString();

        return Inertia::render('dashboard/EmailLogs', [
            'logs' => $logs, 'filters' => $filters,
            'counts' => EmailLog::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status'),
            'delivery' => [
                'mailer' => config('mail.default'), 'queue' => config('queue.default'),
                'localOnly' => in_array(config('mail.mailers.'.config('mail.default').'.transport'), ['log', 'array'], true),
                'staleQueued' => EmailLog::where('status', 'queued')->where('created_at', '<', now()->subMinutes(5))->count(),
            ],
        ]);
    }
}
