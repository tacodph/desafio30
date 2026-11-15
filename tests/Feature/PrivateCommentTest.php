<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PrivateCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_video_comments_are_private_and_staff_can_read(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-17'));
        $author = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($author)->postJson('/comentarios-privados', ['context' => 'video:jantar', 'body' => 'Minha dúvida'])->assertCreated();
        $this->getJson('/comentarios-privados?context=video:jantar')->assertOk()->assertJsonCount(1, 'comments.data');
        $this->actingAs($other)->getJson('/comentarios-privados?context=video:jantar')->assertOk()->assertJsonCount(0, 'comments.data');

        foreach ([UserRole::Nutricionista, UserRole::Administrador] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->create())->getJson('/comentarios-privados?context=video:jantar')
                ->assertOk()->assertJsonPath('comments.data.0.body', 'Minha dúvida');
        }
        foreach ([UserRole::Coach, UserRole::Marketing] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->create())->getJson('/comentarios-privados?context=video:jantar')->assertForbidden();
        }
    }

    public function test_only_owner_can_reply_to_existing_nutri_comment(): void
    {
        $author = User::factory()->create();
        $other = User::factory()->create();
        $author->checkins()->create(['day' => 1, 'meal_comment' => 'Ótima refeição!']);
        $context = "meal:{$author->id}:1";
        $this->actingAs($author)->postJson('/comentarios-privados', ['context' => $context, 'body' => 'Obrigada!'])->assertCreated();
        $this->actingAs($other)->getJson('/comentarios-privados?context='.$context)->assertForbidden();
        $this->postJson('/comentarios-privados', ['context' => $context, 'body' => 'Não sou o autor'])->assertForbidden();
        $this->actingAs($author)->postJson('/comentarios-privados', ['context' => "meal:{$author->id}:2", 'body' => 'Sem comentário'])->assertNotFound();
        foreach ([UserRole::Nutricionista, UserRole::Administrador] as $role) {
            $this->actingAs(User::factory()->ofRole($role)->create())->getJson('/comentarios-privados?context='.$context)
                ->assertOk()->assertJsonPath('comments.data.0.body', 'Obrigada!');
        }
        $this->assertSame(1, DB::table('private_comments')->count());
    }

    public function test_empty_oversized_and_invalid_context_comments_are_rejected(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-17'));
        $this->actingAs(User::factory()->create());
        foreach (['', '   ', str_repeat('a', 2001)] as $body) {
            $this->postJson('/comentarios-privados', ['context' => 'video:jantar', 'body' => $body])->assertUnprocessable();
        }
        $this->postJson('/comentarios-privados', ['context' => 'video:inexistente', 'body' => 'Oi'])->assertNotFound();
        $this->assertSame(0, DB::table('private_comments')->count());
    }
}
