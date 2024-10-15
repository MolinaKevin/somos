<?php

use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Autenticado puede ver la lista de comercios
it('authenticated user can see commerces list', function () {
    // Crear un usuario y autenticarlo
    $user = User::factory()->create();

    // Crear algunos comercios con atributos adicionales
    Commerce::factory()->count(3)->create([
        'name' => 'Test Commerce',
        'city' => 'Test City',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($user);

    // Acceder a la página de administrar comercios
    $response = $this->get('/admin/commerces');

    // Verificar que la página carga correctamente
    $response->assertStatus(200);

    // Verificar que los comercios están en la página
    $response->assertSee('Test Commerce');
    $response->assertSee('Test City');
    $response->assertSee('1234567890');
});

// Test: Crear Comercio
it('can create a commerce', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos del comercio con atributos adicionales
    $commerceData = [
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true,
    ];

    // Enviar petición para crear un comercio
    $response = $this->post('/admin/commerces', $commerceData);

    // Verificar que la respuesta sea un redirect a la lista de comercios
    $response->assertStatus(302);
    $response->assertRedirect('/admin/commerces');

    // Verificar que el comercio fue creado en la base de datos
    $this->assertDatabaseHas('commerces', [
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true,
    ]);
});

// Test: Ver detalle de comercio
it('can view a commerce detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear un comercio con todos los atributos
    $commerce = Commerce::factory()->create([
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true,
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle del comercio
    $response = $this->get("/admin/commerces/{$commerce->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles del comercio están presentes
    $response->assertSee('Test Commerce');
    $response->assertSee('testcommerce@example.com');
    $response->assertSee('1234567890');
    $response->assertSee('Test City');
});

// Test: Actualizar Comercio
it('can update a commerce', function () {
    // Crear un usuario administrador
    $admin = User::factory()->create();

    // Crear un comercio con atributos adicionales
    $commerce = Commerce::factory()->create([
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true,
    ]);

    // Datos actualizados
    $updatedData = [
        'name' => 'Updated Commerce',
        'email' => 'updatedcommerce@example.com',
        'phone_number' => '0987654321',
        'city' => 'Updated City',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
        'points' => 200,
        'active' => false,
        'accepted' => false,
    ];

    // Autenticar el usuario administrador
    $this->actingAs($admin);

    // Enviar la solicitud para actualizar el comercio
    $response = $this->put("/admin/commerces/{$commerce->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de comercios
    $response->assertStatus(302);
    $response->assertRedirect('/admin/commerces');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'name' => 'Updated Commerce',
        'email' => 'updatedcommerce@example.com',
        'phone_number' => '0987654321',
        'city' => 'Updated City',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
        'points' => 200,
        'active' => false,
        'accepted' => false,
    ]);
});

// Test: Eliminar Comercio
it('can delete a commerce', function () {
    // Crear un comercio
    $commerce = Commerce::factory()->create([
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    // Actuar como el usuario autenticado (por ejemplo, un administrador)
    $response = $this->actingAs($adminUser)->delete("/admin/commerces/{$commerce->id}");

    // Verificar que la respuesta sea un redirect a la lista de comercios
    $response->assertStatus(302);
    $response->assertRedirect('/admin/commerces');

    // Verificar que el comercio fue eliminado de la base de datos
    $this->assertDatabaseMissing('commerces', [
        'id' => $commerce->id,
    ]);
});

