<?php

use App\Models\Category;
use App\Models\Commerce;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cannot create a category without translations', function () {
    $categoryData = Category::factory()->make()->toArray();
    $user = User::factory()->create();

    Bouncer::allow($user)->to('manage-category');

    Sanctum::actingAs($user);

    $response = $this->post('/api/categories', $categoryData);

    $response->assertStatus(302);
    $response->assertSessionHasErrors('translations');
});

it('can update a category', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();

    Bouncer::allow($user)->to('manage-category');
    $updatedData = ['name' => 'Updated Category Name'];

    Sanctum::actingAs($user);

    $response = $this->put("/api/categories/{$category->id}", $updatedData);

    $response->assertStatus(200);

    $updatedCategory = Category::find($category->id);
    $this->assertEquals($updatedData['name'], $updatedCategory->getTranslation('name', 'de'));
});

it('can delete a category', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();

    Bouncer::allow($user)->to('manage-category');
    Sanctum::actingAs($user);

    $response = $this->delete("/api/categories/{$category->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

it('can assign categories to a commerce', function () {
    $commerce = Commerce::factory()->create();
    $categories = Category::factory()->count(3)->create();

    $user = User::factory()->create();
    Bouncer::allow($user)->to('manage-category');

    Sanctum::actingAs($user);

    $response = $this->post("/api/commerces/{$commerce->id}/categories", ['categories' => $categories->pluck('id')->toArray()]);

    $response->assertStatus(200);

    foreach($categories as $category) {
        $this->assertDatabaseHas('category_commerce', ['commerce_id' => $commerce->id, 'category_id' => $category->id]);
    }
});

it('can create a category with translations', function () {
    $categoryData = Category::factory()->make()->toArray();

    $user = User::factory()->create();
    Bouncer::allow($user)->to('manage-category');

    Sanctum::actingAs($user);

    $categoryData['translations'] = [
        'de' => ['name' => $categoryData['name']['de']],
        'es' => ['name' => 'Nombre de Categoría'],
        'fr' => ['name' => 'Nom de Catégorie'],
    ];

    $response = $this->post('/api/categories', $categoryData);

    //dd($response);

    $response->assertStatus(201);

    $createdCategory = Category::find($response['id']);

    $this->assertEquals($categoryData['translations']['es']['name'], $createdCategory->getTranslation('name', 'es'));
    $this->assertEquals($categoryData['translations']['fr']['name'], $createdCategory->getTranslation('name', 'fr'));

});

it('only admin can create a category', function () {
    $categoryData = Category::factory()->make()->toArray();
    $admin = User::factory()->create();
    $user = User::factory()->create();

    // Asignar el rol de 'admin' al usuario $admin
    Bouncer::allow('admin')->everything();
    Bouncer::assign('admin')->to($admin);
    $categoryData['translations'] = [
        'de' => ['name' => $categoryData['name']['de']],
        'es' => ['name' => 'Nombre de Categoría'],
        'fr' => ['name' => 'Nom de Catégorie'],
    ];
    Sanctum::actingAs($user);
    $response = $this->postJson('/api/categories', $categoryData);
    $response->assertStatus(403); // 403 es el código de estado de 'Forbidden'

    Sanctum::actingAs($admin);
    $response = $this->postJson('/api/categories', $categoryData);
    $response->assertStatus(201);
});

