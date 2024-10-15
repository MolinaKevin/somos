<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Cashout;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// Verifica que un usuario autenticado puede ver las cashouts en un comercio específico
it('allows an authenticated user to view cashouts in a specific commerce', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear un comercio y asociarlo al usuario
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    // Crear cashouts asociadas a este usuario y comercio
    $cashouts = Cashout::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las cashouts
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/cashouts");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que se retornan las cashouts esperadas
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($cashouts as $cashout) {
        $response->assertJsonFragment(['id' => $cashout->id]);
    }
});

// Verifica que un usuario no puede ver las cashouts de un comercio al que no tiene acceso
it('prevents a user from viewing cashouts in a commerce they do not have access to', function () {
    // Crear dos usuarios
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    // Crear un comercio y asociarlo solo al segundo usuario
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($anotherUser->id);

    // Crear una cashout asociada al segundo usuario y comercio
    $cashout = Cashout::factory()->create([
        'commerce_id' => $commerce->id,
    ]);

    // Autenticar al primer usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las cashouts
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/cashouts");

    // Verificar que la respuesta sea 401 (Unauthorized)
    $response->assertStatus(401);
});

// Verifica que se pueden filtrar las cashouts por fecha
it('allows filtering cashouts by date', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    $commerce->users()->attach($user->id);

    // Crear cashouts con fechas diferentes
    $cashout1 = Cashout::factory()->create(['commerce_id' => $commerce->id, 'created_at' => now()->subDays(10)]);
    $cashout2 = Cashout::factory()->create(['commerce_id' => $commerce->id, 'created_at' => now()->subDays(5)]);
    $cashout3 = Cashout::factory()->create(['commerce_id' => $commerce->id, 'created_at' => now()->subDays(2)]);

    Sanctum::actingAs($user, ['*']);

    // Filtrar por un rango de fechas que debería devolver solo cashout2
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/cashouts?start_date=".now()->subDays(6)->toDateString()."&end_date=".now()->subDays(4)->toDateString());

    $response->assertStatus(200);

    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($cashout2->id, $responseData[0]['id']);
});

// Verifica que la paginación funciona correctamente en la lista de cashouts
it('allows paginating the cashouts list', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear un comercio y asociarlo al usuario
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    // Crear múltiples cashouts asociadas a este comercio
    $cashouts = Cashout::factory()->count(15)->create([
        'commerce_id' => $commerce->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las cashouts con paginación
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/cashouts?per_page=5");

    // Verificar que la respuesta sea exitosa y contenga la paginación correcta
    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  // Verificar que hay 5 elementos por página
});

