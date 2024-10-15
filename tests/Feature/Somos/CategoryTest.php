<?php

use App\Models\Category;
use App\Models\Commerce;
use App\Models\User;
use App\Services\CategoryService;
use App\Services\CommerceService;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('cannot create a category without translations', function () {
    $categoryData = Category::factory()->make()->toArray();
    $user = User::factory()->create();

    Bouncer::allow($user)->to('manage-category');

    unset($categoryData['translations']);

    $this->expectException(\Illuminate\Validation\ValidationException::class);

    $categoryService = app(CategoryService::class);
    $categoryService->create($categoryData, $user);
});

it('can update a category', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();

    Bouncer::allow($user)->to('manage-category');
    $updatedData = ['name' => 'Updated Category Name'];

    $categoryService = app(CategoryService::class);
    $categoryService->update($category, $updatedData, $user);

    $updatedCategory = Category::find($category->id);
    $this->assertEquals($updatedData['name'], $updatedCategory->getTranslation('name', 'de'));
});

it('can delete a category', function () {
    $category = Category::factory()->create();
    $user = User::factory()->create();

    Bouncer::allow($user)->to('manage-category');

    $categoryService = app(CategoryService::class);
    $categoryService->delete($category, $user);

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

it('can assign categories to a commerce', function () {
    $commerce = Commerce::factory()->create();
    $categories = Category::factory()->count(3)->create();

    $user = User::factory()->create();
    Bouncer::allow($user)->to('manage-category');

    $commerceService = app(CommerceService::class);
    $commerceService->assignCategories($commerce, $categories->pluck('id')->toArray(), $user);

    foreach($categories as $category) {
        $this->assertDatabaseHas('category_commerce', ['commerce_id' => $commerce->id, 'category_id' => $category->id]);
    }
});

