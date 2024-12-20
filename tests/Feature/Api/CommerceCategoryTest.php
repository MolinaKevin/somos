<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Commerce;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('fetches commerces by category IDs', function () {
    
    $categories = Category::factory()->count(3)->create();

    
    $commercesCategory1 = Commerce::factory()->count(2)->create();
    $commercesCategory2 = Commerce::factory()->count(2)->create();
    $commercesCategory3 = Commerce::factory()->count(2)->create();

    $categories[0]->commerces()->attach($commercesCategory1);
    $categories[1]->commerces()->attach($commercesCategory2);
    $categories[2]->commerces()->attach($commercesCategory3);

    
    $filterCategoryIds = [$categories[0]->id, $categories[1]->id];

    
    $response = $this->postJson('/api/commerces/filter-by-categories', [
        'category_ids' => $filterCategoryIds,
    ]);

    
    $response->assertStatus(200);

    
    foreach ($commercesCategory1 as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
    foreach ($commercesCategory2 as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
    foreach ($commercesCategory3 as $commerce) {
        $response->assertJsonMissing(['id' => $commerce->id]);
    }

    
    $response->assertJsonCount(4, 'data'); 
});

it('fetches commerces from a parent category including its children', function () {
    
    $parentCategory = Category::factory()->create();

    
    $childCategories = Category::factory()->count(2)->create(['parent_id' => $parentCategory->id]);

    
    $parentCommerces = Commerce::factory()->count(3)->create();
    $parentCategory->commerces()->attach($parentCommerces);

    
    $childCommerces = Commerce::factory()->count(2)->create();
    foreach ($childCategories as $childCategory) {
        $childCategory->commerces()->attach($childCommerces);
    }

    
    $response = $this->postJson('/api/commerces/filter-by-categories', [
        'category_ids' => [$parentCategory->id],
    ]);

    
    $response->assertStatus(200);

    
    foreach ($parentCommerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }

    
    foreach ($childCommerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
});

it('does not include commerces that are not associated with the selected category or its children', function () {
    
    $parentCategory = Category::factory()->create();

    
    $childCategories = Category::factory()->count(2)->create(['parent_id' => $parentCategory->id]);

    
    $parentCommerces = Commerce::factory()->count(3)->create();
    $parentCategory->commerces()->attach($parentCommerces);

    
    $childCommerces = Commerce::factory()->count(2)->create();
    foreach ($childCategories as $childCategory) {
        $childCategory->commerces()->attach($childCommerces);
    }

    
    $unrelatedCommerces = Commerce::factory()->count(2)->create();

    
    $response = $this->postJson('/api/commerces/filter-by-categories', [
        'category_ids' => [$parentCategory->id],
    ]);

    
    $response->assertStatus(200);

    
    foreach ($unrelatedCommerces as $commerce) {
        $response->assertJsonMissing(['id' => $commerce->id]);
    }
});

it('fetches commerces when multiple categories are selected', function () {
    
    $categories = Category::factory()->count(2)->create();

    
    $childCategories1 = Category::factory()->count(2)->create(['parent_id' => $categories[0]->id]);
    $childCategories2 = Category::factory()->count(2)->create(['parent_id' => $categories[1]->id]);

    
    $commercesCategory1 = Commerce::factory()->count(2)->create();
    $categories[0]->commerces()->attach($commercesCategory1);

    $commercesCategory2 = Commerce::factory()->count(2)->create();
    $categories[1]->commerces()->attach($commercesCategory2);

    $childCommerces1 = Commerce::factory()->count(2)->create();
    foreach ($childCategories1 as $childCategory) {
        $childCategory->commerces()->attach($childCommerces1);
    }

    $childCommerces2 = Commerce::factory()->count(2)->create();
    foreach ($childCategories2 as $childCategory) {
        $childCategory->commerces()->attach($childCommerces2);
    }

    
    $response = $this->postJson('/api/commerces/filter-by-categories', [
        'category_ids' => $categories->pluck('id')->toArray(),
    ]);

    
    $response->assertStatus(200);

    
    foreach ($commercesCategory1 as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
    foreach ($commercesCategory2 as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
    foreach ($childCommerces1 as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
    foreach ($childCommerces2 as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
});

