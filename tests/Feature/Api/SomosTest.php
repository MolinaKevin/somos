<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Somos;
use Laravel\Sanctum\Sanctum;
use App\Models\User;

uses(RefreshDatabase::class);

it('allows an authenticated user to view a specific somos', function () {
    // Crear un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Crear un somos en la base de datos
    $somos = Somos::factory()->create();

    // Hacer la llamada al endpoint para obtener el somos
    $response = $this->getJson("/api/somos/{$somos->id}");
    // Verificar que la respuesta sea exitosa y contiene el somos esperado
    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $somos->id]);
});

// Verifica que un usuario autenticado puede actualizar un somos
it('allows an authenticated user to update a somos', function () {
    // Crear un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Crear un somos en la base de datos
    $somos = Somos::factory()->create([
        'name' => 'Nombre Original',
        'description' => 'Descripción Original',
    ]);

    // Datos actualizados
    $updatedData = [
        'name' => 'Somos Actualizado',
        'description' => 'Descripción actualizada',
    ];

    // Hacer la llamada al endpoint para actualizar el somos
    $response = $this->putJson("/api/somos/{$somos->id}", $updatedData);

    // Verificar que la respuesta sea exitosa y el somos se haya actualizado
    $response->assertStatus(200);
    
    // Verificar que los datos en la base de datos sean los correctos
    $this->assertDatabaseHas('somos', [
        'id' => $somos->id,
        'name' => 'Somos Actualizado',
        'description' => 'Descripción actualizada',
    ]);
});


// Verifica que un usuario autenticado puede eliminar un somos
it('allows an authenticated user to delete a somos', function () {
    // Crear un somos en la base de datos
    $somos = Somos::factory()->create();

    // Autenticar al usuario
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para eliminar el somos
    $response = $this->deleteJson("/api/somos/{$somos->id}");

    // Verificar que la respuesta sea exitosa y el somos se haya eliminado
    $response->assertStatus(204);
    $this->assertDatabaseMissing('somos', ['id' => $somos->id]);
});

// Verifica que la paginación funciona correctamente en la lista de somos
it('allows paginating the somos list', function () {
    // Crear múltiples somos en la base de datos
    $somosList = Somos::factory()->count(20)->create();

    // Autenticar al usuario
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Hacer la llamada al endpoint para obtener la lista con paginación
    $response = $this->getJson('/api/somos?per_page=10');

    //dd($response);

    // Verificar que la respuesta sea exitosa y contenga la paginación correcta
    $response->assertStatus(200);
    $response->assertJsonCount(10, 'data');
});


