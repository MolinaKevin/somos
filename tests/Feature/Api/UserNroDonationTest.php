<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Donation;
use App\Models\Nro;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);


// Verifica que un NRO autenticado puede ver las donaciones recibidas
it('allows an authenticated nro to view received donations', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear una NRO y asociarla al usuario
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    // Crear un comercio
    $commerce = Commerce::factory()->create();

    // Crear donaciones asociadas a este comercio y NRO
    $donations = Donation::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las donaciones
    $response = $this->getJson("/api/user/nros/{$nro->id}/donations");

    // Verificar que la respuesta sea exitosa y que se retornan las donaciones esperadas
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($donations as $donation) {
        $response->assertJsonFragment(['id' => $donation->id]);
    }
});


// Verifica que una NRO autenticada puede ver una donación específica
it('allows an authenticated nro to view a specific donation', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    $commerce = Commerce::factory()->create();
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/nros/{$nro->id}/donations/{$donation->id}");

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $donation->id]);
});

// Verifica que una NRO no puede ver las donaciones de otra NRO a la que no tiene acceso
it('prevents an nro from viewing donations of another nro', function () {
    // Crear dos NROs diferentes
    $nro1 = Nro::factory()->create();
    $nro2 = Nro::factory()->create();

    // Crear un usuario y asociarlo solo con el primer NRO
    $user = User::factory()->create();
    $nro1->users()->attach($user->id);

    // Crear donaciones para el segundo NRO
    $donations = Donation::factory()->count(3)->create([
        'nro_id' => $nro2->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Intentar acceder a las donaciones del segundo NRO
    $response = $this->getJson("/api/user/nros/{$nro2->id}/donations");

    // Verificar que la respuesta sea 401 (Unauthorized)
    $response->assertStatus(401);
});


// Verifica que la paginación funciona correctamente en la lista de donaciones
it('allows paginating the donations list for nro', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    $commerce = Commerce::factory()->create();
    $donations = Donation::factory()->count(15)->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/nros/{$nro->id}/donations?per_page=5");

    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  // Verificar que hay 5 elementos por página
});

