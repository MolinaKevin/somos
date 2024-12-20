<?php

use App\Models\Category;
use App\Models\User;
use App\Models\L10n;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('can create a category with an auto-generated slug', function () {
    $category = Category::factory()->create(['name' => 'New Category']);

    
    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
        'slug' => 'new-category',
    ]);
});


it('can create a category with a custom slug', function () {
    $category = Category::factory()->create([
        'name' => 'Another Category',
        'slug' => 'custom-slug',
    ]);

    
    $this->assertDatabaseHas('categories', [
        'name' => 'Another Category',
        'slug' => 'custom-slug',
    ]);
});


it('can delete a category', function () {
    $category = Category::factory()->create();

    $category->delete();

    
    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});


it('can create a child category', function () {
    $parentCategory = Category::factory()->create(['name' => 'Parent Category']);
    $childCategory = Category::factory()->create([
        'name' => 'Child Category',
        'parent_id' => $parentCategory->id,
    ]);

    
    $this->assertEquals($parentCategory->id, $childCategory->parent_id);

    
    $this->assertTrue($parentCategory->children->contains($childCategory));
});


it('can create a category without a parent', function () {
    $category = Category::factory()->create(['name' => 'Standalone Category', 'parent_id' => null]);

    
    $this->assertNull($category->parent_id);
});


it('can fetch all child categories of a parent', function () {
    $parentCategory = Category::factory()->create(['name' => 'Parent']);
    $childCategories = Category::factory()->count(3)->create(['parent_id' => $parentCategory->id]);

    
    $this->assertCount(3, $parentCategory->children);
    $this->assertEquals($childCategories->pluck('id')->toArray(), $parentCategory->children->pluck('id')->toArray());
});


it('a child category knows its parent', function () {
    $parentCategory = Category::factory()->create(['name' => 'Parent']);
    $childCategory = Category::factory()->create(['name' => 'Child', 'parent_id' => $parentCategory->id]);

    
    $this->assertEquals($parentCategory->id, $childCategory->parent->id);
});

it('can fetch a category with a specific locale', function () {
    
    $category = Category::factory()->create(['slug' => 'example-category']);

    
    L10n::create([
        'locale' => 'en',
        'group' => 'category',
        'key' => 'example-category',
        'value' => 'Example Category',
    ]);

    L10n::create([
        'locale' => 'es',
        'group' => 'category',
        'key' => 'example-category',
        'value' => 'Categoría Ejemplo',
    ]);

    
    $categoryNameEn = $category->getTranslatedName('en');
    expect($categoryNameEn)->toBe('Example Category');

    
    $categoryNameEs = $category->getTranslatedName('es');
    expect($categoryNameEs)->toBe('Categoría Ejemplo');
});

