<?php

use App\Models\Donation;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Autenticado puede ver la lista de donaciones
it('authenticated user can see donations list', function () {
    // Crear un usuario y autenticarlo
    $user = User::factory()->create();

    // Crear un comercio y una NRO
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();

    // Crear algunas donaciones
    Donation::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 100,
        'donated_points' => 50,
        'is_paid' => true,
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($user);

    // Acceder a la página de administrar donaciones
    $response = $this->get('/admin/donations');

    // Verificar que la página carga correctamente
    $response->assertStatus(200);

    // Verificar que las donaciones están en la página
    $response->assertSee('100 puntos');
    $response->assertSee('50 puntos donados');
});

// Test: Crear Donación
it('can create a donation', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos de la donación
    $donationData = [
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => true,
    ];

    // Enviar petición para crear una donación
    $response = $this->post('/admin/donations', $donationData);

    // Verificar que la respuesta sea un redirect a la lista de donaciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/donations');

    // Verificar que la donación fue creada en la base de datos
    $this->assertDatabaseHas('donations', [
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => true,
    ]);
});

// Test: Ver detalle de donación
it('can view a donation detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    
    // Crear un comercio, una NRO y una donación
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => true,
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle de la donación
    $response = $this->get("/admin/donations/{$donation->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles de la donación están presentes
    $response->assertSee('200 puntos');
    $response->assertSee('100 puntos donados');
});

// Test: Actualizar Donación
it('can update a donation', function () {
    // Crear un usuario administrador
    $admin = User::factory()->create();

    // Crear un comercio y una institución (NRO)
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();

    // Crear una donación
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 100,
        'donated_points' => 50,
        'is_paid' => true,
    ]);

    // Datos actualizados para la donación
    $updatedData = [
        'commerce_id' => $commerce->id, // Añadir commerce_id
        'nro_id' => $nro->id,           // Añadir nro_id
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => false,
    ];

    // Autenticar al usuario administrador
    $this->actingAs($admin);

    // Enviar la solicitud para actualizar la donación
    $response = $this->put("/admin/donations/{$donation->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de donaciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/donations');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('donations', [
        'id' => $donation->id,
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => false,
    ]);
});


// Test: Eliminar Donación
it('can delete a donation', function () {
    // Crear un comercio, una NRO y una donación
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 200,
        'donated_points' => 100,
        'is_paid' => true,
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create();

    // Actuar como el usuario autenticado
    $response = $this->actingAs($adminUser)->delete("/admin/donations/{$donation->id}");

    // Verificar que la respuesta sea un redirect a la lista de donaciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/donations');

    // Verificar que la donación fue eliminada de la base de datos
    $this->assertDatabaseMissing('donations', [
        'id' => $donation->id,
    ]);
});

