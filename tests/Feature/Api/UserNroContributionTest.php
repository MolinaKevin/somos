<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Nro;
use App\Models\Contribution;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

// Verifica que un NRO autenticado puede ver las contribuciones realizadas
it('allows an authenticated nro to view made contributions', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear una NRO y asociarla al usuario
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    // Crear contribuciones asociadas a esta NRO
    $contributions = Contribution::factory()->count(3)->create([
        'nro_id' => $nro->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener las contribuciones
    $response = $this->getJson("/api/user/nros/{$nro->id}/contributions");

    // Verificar que la respuesta sea exitosa y que se retornan las contribuciones esperadas
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($contributions as $contribution) {
        $response->assertJsonFragment(['id' => $contribution->id]);
    }
});

// Verifica que una NRO autenticada puede ver una contribución específica
it('allows an authenticated nro to view a specific contribution', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    $contribution = Contribution::factory()->create([
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/nros/{$nro->id}/contributions/{$contribution->id}");

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $contribution->id]);
});

// Verifica que una NRO no puede ver las contribuciones de otra NRO a la que no tiene acceso
it('prevents an nro from viewing contributions of another nro', function () {
    // Crear dos NROs diferentes
    $nro1 = Nro::factory()->create();
    $nro2 = Nro::factory()->create();

    // Crear un usuario y asociarlo solo con el primer NRO
    $user = User::factory()->create();
    $nro1->users()->attach($user->id);

    // Crear contribuciones para el segundo NRO
    $contributions = Contribution::factory()->count(3)->create([
        'nro_id' => $nro2->id,
    ]);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Intentar acceder a las contribuciones del segundo NRO
    $response = $this->getJson("/api/user/nros/{$nro2->id}/contributions");

    // Verificar que la respuesta sea 403 (Forbidden)
    $response->assertStatus(403);
});


// Verifica que la paginación funciona correctamente en la lista de contribuciones
it('allows paginating the contributions list for nro', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    $contributions = Contribution::factory()->count(15)->create([
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/nros/{$nro->id}/contributions?per_page=5");

    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  // Verificar que hay 5 elementos por página
});

