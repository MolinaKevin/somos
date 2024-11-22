<?php

use App\Models\Category;
use App\Models\User;
use App\Models\L10n;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Crear una categoría con slug generado automáticamente
it('can create a category with an auto-generated slug', function () {
    $category = Category::factory()->create(['name' => 'New Category']);

    // Verificar que la categoría fue creada con el slug correcto
    $this->assertDatabaseHas('categories', [
        'name' => 'New Category',
        'slug' => 'new-category',
    ]);
});

// Test: Crear una categoría con un slug personalizado
it('can create a category with a custom slug', function () {
    $category = Category::factory()->create([
        'name' => 'Another Category',
        'slug' => 'custom-slug',
    ]);

    // Verificar que el slug personalizado se usó
    $this->assertDatabaseHas('categories', [
        'name' => 'Another Category',
        'slug' => 'custom-slug',
    ]);
});

// Test: Eliminar una categoría
it('can delete a category', function () {
    $category = Category::factory()->create();

    $category->delete();

    // Verificar que la categoría fue eliminada
    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

// Test: Crear una categoría hija (relación padre-hijo)
it('can create a child category', function () {
    $parentCategory = Category::factory()->create(['name' => 'Parent Category']);
    $childCategory = Category::factory()->create([
        'name' => 'Child Category',
        'parent_id' => $parentCategory->id,
    ]);

    // Verificar que la categoría hija tiene el padre correcto
    $this->assertEquals($parentCategory->id, $childCategory->parent_id);

    // Verificar que el padre tiene a la hija en la relación
    $this->assertTrue($parentCategory->children->contains($childCategory));
});

// Test: Crear una categoría sin padre
it('can create a category without a parent', function () {
    $category = Category::factory()->create(['name' => 'Standalone Category', 'parent_id' => null]);

    // Verificar que no tiene padre
    $this->assertNull($category->parent_id);
});

// Test: Obtener todas las categorías hijas de una categoría padre
it('can fetch all child categories of a parent', function () {
    $parentCategory = Category::factory()->create(['name' => 'Parent']);
    $childCategories = Category::factory()->count(3)->create(['parent_id' => $parentCategory->id]);

    // Verificar que el padre tiene 3 hijos
    $this->assertCount(3, $parentCategory->children);
    $this->assertEquals($childCategories->pluck('id')->toArray(), $parentCategory->children->pluck('id')->toArray());
});

// Test: Verificar que una categoría hija conoce a su padre
it('a child category knows its parent', function () {
    $parentCategory = Category::factory()->create(['name' => 'Parent']);
    $childCategory = Category::factory()->create(['name' => 'Child', 'parent_id' => $parentCategory->id]);

    // Verificar que el padre de la categoría hija es correcto
    $this->assertEquals($parentCategory->id, $childCategory->parent->id);
});

it('can fetch a category with a specific locale', function () {
    // Crear una categoría
    $category = Category::factory()->create(['slug' => 'example-category']);

    // Crear traducciones en diferentes locales
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

    // Buscar la categoría con el locale "en"
    $categoryNameEn = $category->getTranslatedName('en');
    expect($categoryNameEn)->toBe('Example Category');

    // Buscar la categoría con el locale "es"
    $categoryNameEs = $category->getTranslatedName('es');
    expect($categoryNameEs)->toBe('Categoría Ejemplo');
});

