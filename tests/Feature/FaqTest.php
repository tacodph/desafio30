<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Faq;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitors_only_see_published_questions_in_order(): void
    {
        Faq::query()->delete();
        Faq::create(['question' => 'Segunda?', 'answer' => 'Resposta 2', 'sort_order' => 2, 'published' => true]);
        Faq::create(['question' => 'Primeira?', 'answer' => 'Resposta 1', 'sort_order' => 1, 'published' => true]);
        Faq::create(['question' => 'Oculta?', 'answer' => 'Rascunho', 'sort_order' => 0, 'published' => false]);

        $this->get(route('faq'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('Faq')->has('faqs', 2)->where('faqs.0.question', 'Primeira?')->where('faqs.1.question', 'Segunda?'));
    }

    public function test_admin_can_create_and_edit_questions(): void
    {
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create());
        $this->post(route('admin.faqs.store'), ['question' => 'Nova pergunta?', 'answer' => 'Resposta inicial', 'sort_order' => 20, 'published' => true])
            ->assertRedirect(route('admin.faqs'));
        $faq = Faq::where('question', 'Nova pergunta?')->firstOrFail();
        $this->put(route('admin.faqs.update', $faq), ['question' => 'Pergunta revisada?', 'answer' => 'Resposta revisada', 'sort_order' => 5, 'published' => false])
            ->assertRedirect(route('admin.faqs'));
        $this->assertDatabaseHas('faqs', ['id' => $faq->id, 'answer' => 'Resposta revisada', 'published' => false]);
        $this->get(route('admin.faqs'))->assertOk();
    }

    public function test_other_roles_cannot_manage_questions(): void
    {
        $faq = Faq::firstOrFail();
        foreach ([UserRole::Atleta, UserRole::Nutricionista, UserRole::Coach, UserRole::Marketing] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->create());
            $this->get(route('admin.faqs'))->assertForbidden();
            $this->post(route('admin.faqs.store'), [])->assertForbidden();
            $this->put(route('admin.faqs.update', $faq), [])->assertForbidden();
        }
    }

    public function test_invalid_question_is_not_saved(): void
    {
        $this->actingAs(User::factory()->ofRole(UserRole::Administrador)->create());
        $this->post(route('admin.faqs.store'), ['question' => '', 'answer' => '', 'sort_order' => -1, 'published' => 'invalid'])
            ->assertSessionHasErrors(['question', 'answer', 'sort_order', 'published']);
    }
}
