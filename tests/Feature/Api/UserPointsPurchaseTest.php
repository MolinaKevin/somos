<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\PointsPurchase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// Test: Verifica que un usuario autenticado puede ver sus compras de puntos
it('allows an authenticated user to view their point purchases', function () {
    // Crear un usuario
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Crear compras de puntos asociadas al usuario
    $pointPurchases = PointsPurchase::factory()->count(3)->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las compras de puntos del usuario
    $response = $this->getJson("/api/user/point-purchases");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que se retornan las compras de puntos esperadas
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($pointPurchases as $pointPurchase) {
        $response->assertJsonFragment(['id' => $pointPurchase->id]);
    }
});

// Test: Verifica que un usuario puede filtrar las compras de puntos por fecha
it('allows filtering point purchases by date', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Crear compras de puntos con fechas diferentes
    $purchase1 = PointsPurchase::factory()->create(['user_id' => $user->id, 'commerce_id' => $commerce->id, 'created_at' => now()->subDays(10)]);
    $purchase2 = PointsPurchase::factory()->create(['user_id' => $user->id, 'commerce_id' => $commerce->id, 'created_at' => now()->subDays(5)]);
    $purchase3 = PointsPurchase::factory()->create(['user_id' => $user->id, 'commerce_id' => $commerce->id, 'created_at' => now()->subDays(2)]);

    Sanctum::actingAs($user, ['*']);

    // Filtrar por un rango de fechas que debería devolver solo purchase2
    $response = $this->getJson("/api/user/point-purchases?start_date=".now()->subDays(6)->toDateString()."&end_date=".now()->subDays(4)->toDateString());

    $response->assertStatus(200);

    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($purchase2->id, $responseData[0]['id']);
});

// Test: Verifica que la paginación funciona correctamente en la lista de compras de puntos
it('allows paginating the point purchases list', function () {
    // Crear un usuario
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Crear múltiples compras de puntos asociadas a este usuario
    $pointPurchases = PointsPurchase::factory()->count(15)->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las compras de puntos con paginación
    $response = $this->getJson("/api/user/point-purchases?per_page=5");

    // Verificar que la respuesta sea exitosa y contenga la paginación correcta
    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  // Verificar que hay 5 elementos por página
});

// Test: Verifica que un usuario pueda eliminar una compra de puntos
it('allows an authenticated user to delete a point purchase', function () {
    // Crear un usuario
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Crear una compra de puntos asociada al usuario
    $pointPurchase = PointsPurchase::factory()->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para eliminar la compra de puntos
    $response = $this->deleteJson("/api/user/point-purchases/{$pointPurchase->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la compra de puntos fue eliminada de la base de datos
    $this->assertDatabaseMissing('points_purchases', [
        'id' => $pointPurchase->id,
    ]);
});

