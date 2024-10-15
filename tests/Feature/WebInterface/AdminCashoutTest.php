<?php

use App\Models\Cashout;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Autenticado puede ver la lista de cashouts
it('authenticated user can see cashouts list', function () {
    // Crear un usuario y autenticarlo
    $user = User::factory()->create();

    // Crear un comercio
    $commerce = Commerce::factory()->create();

    // Crear algunos cashouts
    Cashout::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'points' => 100,
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($user);

    // Acceder a la página de administrar cashouts
    $response = $this->get('/admin/cashouts');

    // Verificar que la página carga correctamente
    $response->assertStatus(200);

    // Verificar que los cashouts están en la página
    $response->assertSee('100 puntos');
});

// Test: Crear Cashout
it('can create a cashout', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos del cashout
    $cashoutData = [
        'commerce_id' => $commerce->id,
        'points' => 200,
    ];

    // Enviar petición para crear un cashout
    $response = $this->post('/admin/cashouts', $cashoutData);

    // Verificar que la respuesta sea un redirect a la lista de cashouts
    $response->assertStatus(302);
    $response->assertRedirect('/admin/cashouts');

    // Verificar que el cashout fue creado en la base de datos
    $this->assertDatabaseHas('cashouts', [
        'commerce_id' => $commerce->id,
        'points' => 200,
    ]);
});

// Test: Ver detalle de cashout
it('can view a cashout detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    
    // Crear un comercio y un cashout
    $commerce = Commerce::factory()->create();
    $cashout = Cashout::factory()->create([
        'commerce_id' => $commerce->id,
        'points' => 200,
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle del cashout
    $response = $this->get("/admin/cashouts/{$cashout->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles del cashout están presentes
    $response->assertSee('200 puntos');
});

// Test: Actualizar Cashout
it('can update a cashout', function () {
    // Crear un usuario administrador
    $admin = User::factory()->create();

    // Crear un comercio y un cashout
    $commerce = Commerce::factory()->create();
    $cashout = Cashout::factory()->create([
        'commerce_id' => $commerce->id,
        'points' => 100,
    ]);

    // Datos actualizados para el cashout
    $updatedData = [
        'commerce_id' => $commerce->id, 
        'points' => 200,
    ];

    // Autenticar al usuario administrador
    $this->actingAs($admin);

    // Enviar la solicitud para actualizar el cashout
    $response = $this->put("/admin/cashouts/{$cashout->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de cashouts
    $response->assertStatus(302);
    $response->assertRedirect('/admin/cashouts');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('cashouts', [
        'id' => $cashout->id,
        'commerce_id' => $commerce->id,
        'points' => 200,
    ]);
});

// Test: Eliminar Cashout
it('can delete a cashout', function () {
    // Crear un comercio y un cashout
    $commerce = Commerce::factory()->create();
    $cashout = Cashout::factory()->create([
        'commerce_id' => $commerce->id,
        'points' => 200,
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create();

    // Actuar como el usuario autenticado
    $response = $this->actingAs($adminUser)->delete("/admin/cashouts/{$cashout->id}");

    // Verificar que la respuesta sea un redirect a la lista de cashouts
    $response->assertStatus(302);
    $response->assertRedirect('/admin/cashouts');

    // Verificar que el cashout fue eliminado de la base de datos
    $this->assertDatabaseMissing('cashouts', [
        'id' => $cashout->id,
    ]);
});

