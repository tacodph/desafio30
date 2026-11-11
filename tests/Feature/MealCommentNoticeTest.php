<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class MealCommentNoticeTest extends TestCase
{
    use RefreshDatabase;

    public function test_commented_checkins_are_shown_once_each_after_login(): void
    {
        $athlete = User::factory()->create();
        $first = $athlete->checkins()->create(['day' => 1, 'meal_comment' => 'Muito bom!']);
        $second = $athlete->checkins()->create(['day' => 2, 'meal_comment' => 'Continue assim!']);
        $athlete->checkins()->create(['day' => 3]);

        $this->post('/login', ['email' => $athlete->email, 'password' => 'password'])
            ->assertSessionHas('meal_comment_notice_ids', [$first->id, $second->id]);

        $this->get(route('meus-objetivos.show', 'treino'))->assertInertia(fn (Assert $page) => $page
            ->has('mealCommentNotices.checkins', 2)
            ->where('mealCommentNotices.checkins.0.day', 1));

        $this->postJson(route('athlete.meal-comment-notice', $first->id))->assertOk()->assertJson(['show' => true]);
        $this->postJson(route('athlete.meal-comment-notice', $first->id))->assertOk()->assertJson(['show' => false]);
        $this->assertNotNull($first->fresh()->meal_comment_notice_shown_at);
        $this->assertNull($second->fresh()->meal_comment_notice_shown_at);

        $first->update(['meal_comment' => 'Comentário editado']);
        $this->post('/logout');
        $this->post('/login', ['email' => $athlete->email, 'password' => 'password'])
            ->assertSessionHas('meal_comment_notice_ids', [$second->id]);
    }

    public function test_new_comments_wait_for_the_next_login(): void
    {
        $athlete = User::factory()->create();
        $checkin = $athlete->checkins()->create(['day' => 1]);
        $this->post('/login', ['email' => $athlete->email, 'password' => 'password']);
        $checkin->update(['meal_comment' => 'Boa refeição!']);

        $this->get(route('meus-objetivos.show', 'treino'))->assertInertia(fn (Assert $page) => $page
            ->has('mealCommentNotices.checkins', 0));
        $this->postJson(route('athlete.meal-comment-notice', $checkin->id))->assertNotFound();

        $this->post('/logout');
        $this->post('/login', ['email' => $athlete->email, 'password' => 'password'])
            ->assertSessionHas('meal_comment_notice_ids', [$checkin->id]);
    }

    public function test_athletes_cannot_claim_another_athletes_notice(): void
    {
        $athlete = User::factory()->create();
        $other = User::factory()->create();
        $checkin = $other->checkins()->create(['day' => 1, 'meal_comment' => 'Ótimo!']);

        $this->actingAs($athlete)->withSession(['meal_comment_notice_ids' => [$checkin->id]])
            ->postJson(route('athlete.meal-comment-notice', $checkin->id))
            ->assertOk()->assertJson(['show' => false]);
        $this->assertNull($checkin->fresh()->meal_comment_notice_shown_at);
    }

    public function test_staff_cannot_claim_notices(): void
    {
        $staff = User::factory()->ofRole(UserRole::Nutricionista)->create();
        $this->actingAs($staff)->postJson(route('athlete.meal-comment-notice', 1))->assertForbidden();
    }
}
