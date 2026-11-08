<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FaqController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Faq', [
            'faqs' => Faq::query()->where('published', true)->orderBy('sort_order')->orderBy('id')->get(['id', 'question', 'answer']),
        ]);
    }

    public function admin(Request $request): Response
    {
        $this->authorizeAdmin($request);

        return Inertia::render('dashboard/Faqs', [
            'faqs' => Faq::query()->orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeAdmin($request);
        Faq::create($this->validated($request));

        return to_route('admin.faqs');
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $this->authorizeAdmin($request);
        $faq->update($this->validated($request));

        return to_route('admin.faqs');
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->userType?->name === UserRole::Administrador->value, 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:10000'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:99999'],
            'published' => ['required', 'boolean'],
        ]);
    }
}
