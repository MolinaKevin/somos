<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Purchase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// Verifica que un usuario autenticado puede ver las compras en un comercio específico
it('allows an authenticated user to view purchases in a specific commerce', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear un comercio y asociarlo al usuario
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    // Crear compras asociadas a este usuario y comercio
    $purchases = Purchase::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las compras
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/purchases");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que se retornan las compras esperadas
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($purchases as $purchase) {
        $response->assertJsonFragment(['id' => $purchase->id]);
    }
});

// Verifica que un usuario no puede ver las compras de un comercio al que no tiene acceso
it('prevents a user from viewing purchases in a commerce they do not have access to', function () {
    // Crear dos usuarios
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    // Crear un comercio y asociarlo solo al segundo usuario
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($anotherUser->id);

    // Crear una compra asociada al segundo usuario y comercio
    $purchase = Purchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $anotherUser->id,
    ]);

    // Autenticar al primer usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las compras
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/purchases");

    // Verificar que la respuesta sea 401 (Unauthorized)
    $response->assertStatus(401);
});

// Verifica que se pueden filtrar las compras por fecha
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
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/purchases?start_date=".now()->subDays(6)->toDateString()."&end_date=".now()->subDays(4)->toDateString());

    $response->assertStatus(200);

    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($purchase2->id, $responseData[0]['id']);
});


// Verifica que la paginación funciona correctamente en la lista de compras
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
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/purchases?per_page=5");

    // Verificar que la respuesta sea exitosa y contenga la paginación correcta
    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  // Verificar que hay 5 elementos por página
});

