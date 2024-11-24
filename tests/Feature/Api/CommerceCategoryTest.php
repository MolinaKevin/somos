<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Commerce;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('fetches commerces by category IDs', function () {
    // Crear categorías
    $categories = Category::factory()->count(3)->create();

    // Crear comercios y asociarlos a categorías
    $commercesCategory1 = Commerce::factory()->count(2)->create();
    $commercesCategory2 = Commerce::factory()->count(2)->create();
    $commercesCategory3 = Commerce::factory()->count(2)->create();

    $categories[0]->commerces()->attach($commercesCategory1);
    $categories[1]->commerces()->attach($commercesCategory2);
    $categories[2]->commerces()->attach($commercesCategory3);

    // Seleccionar categorías a filtrar
    $filterCategoryIds = [$categories[0]->id, $categories[1]->id];

    // Hacer una solicitud al endpoint con el array de categorías
    $response = $this->postJson('/api/commerces/filter-by-categories', [
        'category_ids' => $filterCategoryIds,
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Verificar que solo se devuelven comercios de las categorías filtradas
    foreach ($commercesCategory1 as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
    foreach ($commercesCategory2 as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
    foreach ($commercesCategory3 as $commerce) {
        $response->assertJsonMissing(['id' => $commerce->id]);
    }

    // Verificar que el conteo es correcto
    $response->assertJsonCount(4, 'data'); // 2 comercios por categoría (categorías 0 y 1)
});

it('fetches commerces from a parent category including its children', function () {
    // Crear una categoría padre
    $parentCategory = Category::factory()->create();

    // Crear categorías hijas
    $childCategories = Category::factory()->count(2)->create(['parent_id' => $parentCategory->id]);

    // Crear comercios asociados a la categoría padre
    $parentCommerces = Commerce::factory()->count(3)->create();
    $parentCategory->commerces()->attach($parentCommerces);

    // Crear comercios asociados a las categorías hijas
    $childCommerces = Commerce::factory()->count(2)->create();
    foreach ($childCategories as $childCategory) {
        $childCategory->commerces()->attach($childCommerces);
    }

    // Hacer una solicitud para obtener comercios de la categoría padre
    $response = $this->postJson('/api/commerces/filter-by-categories', [
        'category_ids' => [$parentCategory->id],
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Verificar que se incluyan los comercios de la categoría padre
    foreach ($parentCommerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }

    // Verificar que también se incluyan los comercios de las categorías hijas
    foreach ($childCommerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
});

it('does not include commerces that are not associated with the selected category or its children', function () {
    // Crear una categoría padre
    $parentCategory = Category::factory()->create();

    // Crear categorías hijas
    $childCategories = Category::factory()->count(2)->create(['parent_id' => $parentCategory->id]);

    // Crear comercios asociados a la categoría padre
    $parentCommerces = Commerce::factory()->count(3)->create();
    $parentCategory->commerces()->attach($parentCommerces);

    // Crear comercios asociados a las categorías hijas
    $childCommerces = Commerce::factory()->count(2)->create();
    foreach ($childCategories as $childCategory) {
        $childCategory->commerces()->attach($childCommerces);
    }

    // Crear comercios no relacionados
    $unrelatedCommerces = Commerce::factory()->count(2)->create();

    // Hacer una solicitud para obtener comercios de la categoría padre
    $response = $this->postJson('/api/commerces/filter-by-categories', [
        'category_ids' => [$parentCategory->id],
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Verificar que los comercios no relacionados no estén incluidos
    foreach ($unrelatedCommerces as $commerce) {
        $response->assertJsonMissing(['id' => $commerce->id]);
    }
});

it('fetches commerces when multiple categories are selected', function () {
    // Crear categorías principales
    $categories = Category::factory()->count(2)->create();

    // Crear categorías hijas para cada categoría principal
    $childCategories1 = Category::factory()->count(2)->create(['parent_id' => $categories[0]->id]);
    $childCategories2 = Category::factory()->count(2)->create(['parent_id' => $categories[1]->id]);

    // Crear comercios asociados a las categorías principales e hijas
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

    // Hacer una solicitud para obtener comercios de ambas categorías principales
    $response = $this->postJson('/api/commerces/filter-by-categories', [
        'category_ids' => $categories->pluck('id')->toArray(),
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Verificar que se incluyan todos los comercios relacionados
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

