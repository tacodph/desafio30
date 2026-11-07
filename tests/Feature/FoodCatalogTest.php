<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Food;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FoodCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_admin_registers_a_food_inside_a_category_from_the_shopping_list(): void
    {
        $admin = User::factory()->ofRole(UserRole::Administrador)->create();

        $this->actingAs(User::factory()->create())->get('/admin/alimentos')->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/alimentos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('groups.0.category', '1. Proteínas')
                ->where('groups.0.name', 'Carnes bovinas')
                ->where('groups.0.foods.0.name', 'Patinho bovino sem gordura'));

        $this->actingAs($admin)
            ->post('/admin/alimentos', [
                'category' => '1. Proteínas',
                'group' => 'Carnes bovinas',
                'name' => 'Cupim',
            ])
            ->assertRedirect('/admin/alimentos');

        $this->assertDatabaseHas('foods', ['name' => 'Cupim', 'active' => true]);

        $patinho = Food::query()->where('name', 'Patinho bovino sem gordura')->first();

        $this->actingAs($admin)
            ->patch('/admin/alimentos/'.$patinho->id)
            ->assertRedirect('/admin/alimentos');

        $this->assertFalse($patinho->fresh()->active);

        $this->get('/objetivos/alimentacao/compras')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('catalog.0.groups.0.items.0', 'Lagarto bovino'));

        $this->actingAs($admin)
            ->get('/admin/alimentos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('groups.0.foods.0.active', false));

        $this->actingAs($admin)->patch('/admin/alimentos/'.$patinho->id);

        $food = Food::query()->where('name', 'Cupim')->first();

        $this->actingAs($admin)
            ->delete('/admin/alimentos/'.$food->id)
            ->assertRedirect('/admin/alimentos');

        $this->assertDatabaseMissing('foods', ['name' => 'Cupim']);
        $this->assertDatabaseHas('foods', ['name' => 'Patinho bovino sem gordura']);

        $this->get('/objetivos/alimentacao/compras')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->where('catalog.0.title', '1. Proteínas'));
    }
}
