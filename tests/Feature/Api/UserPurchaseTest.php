<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Purchase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// Test: Verifica que un usuario autenticado puede ver sus compras
it('allows an authenticated user to view their purchases', function () {
    // Crear un usuario
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Crear compras asociadas al usuario
    $purchases = Purchase::factory()->count(3)->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las compras del usuario
    $response = $this->getJson("/api/user/purchases");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que se retornan las compras esperadas
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($purchases as $purchase) {
        $response->assertJsonFragment(['id' => $purchase->id]);
    }
});

it('allows filtering purchases by date', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    $commerce->users()->attach($user->id);

    // Crear compras con fechas diferentes
    $purchase1 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(10)]);
    $purchase2 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(5)]);
    $purchase3 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(2)]);

    Sanctum::actingAs($user, ['*']);

    // Filtrar por un rango de fechas que debería devolver solo purchase2
    $response = $this->getJson("/api/user/purchases?start_date=".now()->subDays(6)->toDateString()."&end_date=".now()->subDays(4)->toDateString());

    $response->assertStatus(200);

    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($purchase2->id, $responseData[0]['id']);
});

it('allows paginating the purchases list', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear un comercio y asociarlo al usuario
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    // Crear múltiples compras asociadas a este usuario y comercio
    $purchases = Purchase::factory()->count(15)->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las compras con paginación
    $response = $this->getJson("/api/user/purchases?per_page=5");

    // Verificar que la respuesta sea exitosa y contenga la paginación correcta
    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  // Verificar que hay 5 elementos por página
});


// Test: Verifica que un usuario pueda eliminar una compra
it('allows an authenticated user to delete a purchase', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear una compra asociada al usuario
    $purchase = Purchase::factory()->create([
        'user_id' => $user->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para eliminar la compra
    $response = $this->deleteJson("/api/user/purchases/{$purchase->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la compra fue eliminada de la base de datos
    $this->assertDatabaseMissing('purchases', [
        'id' => $purchase->id,
    ]);
});

// Test: Verifica que las compras incluyen los puntos recibidos por el usuario
it('includes user points received for each purchase', function () {
    // Crear un usuario y un comercio
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Crear compras asociadas al usuario con registros en purchase_user_points
    $purchases = Purchase::factory()->count(3)->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    // Asocia puntos a cada compra para el usuario
    foreach ($purchases as $purchase) {
        \DB::table('purchase_user_points')->insert([
            'purchase_id' => $purchase->id,
            'user_id' => $user->id,
            'points' => 100,  // Puedes ajustar el valor según sea necesario para el test
        ]);
    }

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las compras del usuario
    $response = $this->getJson("/api/user/purchases");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que cada compra incluye los puntos recibidos por el usuario
    $responseData = $response->json('data');
    foreach ($responseData as $purchaseData) {
        $this->assertArrayHasKey('user_points_received', $purchaseData);
        $this->assertEquals(100, $purchaseData['user_points_received']); // Verifica que coincide con el valor insertado
    }
});

