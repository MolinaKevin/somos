<?php

use App\Models\Category;
use App\Models\L10n;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('fetches all categories with user locale translations', function () {
    
    $categories = Category::factory()->count(3)->create();

    
    foreach ($categories as $category) {
        L10n::factory()->create([
            'locale' => 'es',
            'group' => 'category',
            'key' => $category->slug,
            'value' => $category->name . ' (ES)',
        ]);
        L10n::factory()->create([
            'locale' => 'en',
            'group' => 'category',
            'key' => $category->slug,
            'value' => $category->name . ' (EN)',
        ]);
    }

    
    $user = User::factory()->create(['language' => 'es']);

    
    $response = $this->actingAs($user)->getJson('/api/categories');

    
    $response->assertStatus(200);
    $response->assertJsonFragment(['translated_name' => $categories[0]->name . ' (ES)']);
    $response->assertJsonFragment(['translated_name' => $categories[1]->name . ' (ES)']);
    $response->assertJsonFragment(['translated_name' => $categories[2]->name . ' (ES)']);
});

it('fetches all categories in English when no user is authenticated', function () {
    
    $categories = Category::factory()->count(3)->create();

    
    foreach ($categories as $category) {
        L10n::factory()->create([
            'locale' => 'en',
            'group' => 'category',
            'key' => $category->slug,
            'value' => $category->name . ' (EN)',
        ]);
    }

    
    $response = $this->get('/api/categories');

    
    $response->assertStatus(200);
    $response->assertJsonFragment(['translated_name' => $categories[0]->name . ' (EN)']);
    $response->assertJsonFragment(['translated_name' => $categories[1]->name . ' (EN)']);
    $response->assertJsonFragment(['translated_name' => $categories[2]->name . ' (EN)']);
});

it('fetches commerces from a category and its children', function () {
    
    $parentCategory = Category::factory()->create();

    
    $childCategories = Category::factory()->count(2)->create(['parent_id' => $parentCategory->id]);

    
    $commerces = Commerce::factory()->count(3)->create();
    $childCommerces = Commerce::factory()->count(2)->create();

    
    $parentCategory->commerces()->attach($commerces);
    $childCategories[0]->commerces()->attach($childCommerces);

    
    $response = $this->getJson("/api/categories/{$parentCategory->id}/commerces");

    
    $response->assertStatus(200);
    foreach ($commerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
    foreach ($childCommerces as $childCommerce) {
        $response->assertJsonFragment(['id' => $childCommerce->id]);
    }
});

it('fetches child categories and associated commerces', function () {
    $parentCategory = Category::factory()->create();

    $childCategories = Category::factory()->count(3)->create(['parent_id' => $parentCategory->id]);

    $parentCommerces = Commerce::factory()->count(2)->create();
    $parentCategory->commerces()->attach($parentCommerces);

    $childCommerces = Commerce::factory()->count(3)->create();
    foreach ($childCategories as $childCategory) {
        $childCategory->commerces()->attach($childCommerces);
    }

    $response = $this->getJson("/api/categories/{$parentCategory->id}/details");

    $response->assertStatus(200);

    foreach ($childCategories as $childCategory) {
        $response->assertJsonFragment(['id' => $childCategory->id]);
    }

    foreach ($parentCommerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }

    foreach ($childCommerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
});


