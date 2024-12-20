<?php

use App\Models\Category;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);
});

it('can list categories in the admin panel', function () {
    
    Category::factory()->count(3)->create();

    
    $response = $this->get('/admin/categories');

    $response->assertStatus(200);
    $response->assertSee(Category::first()->name);
});

it('can create a category with an auto-generated slug', function () {
    $response = $this->post('/admin/categories', [
        'name' => 'New Category',
    ]);

    $response->assertRedirect('/admin/categories');
    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
        'slug' => 'new-category',
    ]);
});

it('can create a category with a custom slug', function () {
    $response = $this->post('/admin/categories', [
        'name' => 'Another Category',
        'slug' => 'custom-slug',
    ]);

    $response->assertRedirect('/admin/categories');
    $this->assertDatabaseHas('categories', [
        'name' => 'Another Category',
        'slug' => 'custom-slug',
    ]);
});

it('can update a category', function () {
    $category = Category::factory()->create([
        'name' => 'Old Name',
    ]);

    $response = $this->put("/admin/categories/{$category->id}", [
        'name' => 'Updated Name',
    ]);

    $response->assertRedirect('/admin/categories');
    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Updated Name',
        'slug' => 'updated-name',
    ]);
});

it('can delete a category', function () {
    $category = Category::factory()->create();

    $response = $this->delete("/admin/categories/{$category->id}");

    $response->assertRedirect('/admin/categories');
    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

it('can create a child category', function () {
    $parentCategory = Category::factory()->create(['name' => 'Parent Category']);

    $response = $this->post('/admin/categories', [
        'name' => 'Child Category',
        'parent_id' => $parentCategory->id,
    ]);

    $response->assertRedirect('/admin/categories');
    $childCategory = Category::where('name', 'Child Category')->first();

    
    $this->assertEquals($parentCategory->id, $childCategory->parent_id);

    
    $this->assertTrue($parentCategory->children->contains($childCategory));
});

it('does not allow duplicate slugs', function () {
    Category::factory()->create(['slug' => 'duplicate-slug']);

    $response = $this->post('/admin/categories', [
        'name' => 'Duplicate Name',
        'slug' => 'duplicate-slug',
    ]);

    $response->assertSessionHasErrors('slug');
    $this->assertCount(1, Category::where('slug', 'duplicate-slug')->get());
});

it('can view children categories', function () {
    $parentCategory = Category::factory()->create();
    $childCategories = Category::factory()->count(3)->create(['parent_id' => $parentCategory->id]);

    $response = $this->get(route('admin.categories.children', $parentCategory->id));
    $response->assertStatus(200);

    foreach ($childCategories as $child) {
        $response->assertSee($child->name);
    }
});

it('can view commerces in a category', function () {
    $category = Category::factory()->create();
    $commerces = Commerce::factory()->count(3)->create();
    $category->commerces()->attach($commerces);

    $response = $this->get(route('admin.categories.commerces', $category->id));
    $response->assertStatus(200);

    foreach ($commerces as $commerce) {
        $response->assertSee($commerce->name);
    }
});

