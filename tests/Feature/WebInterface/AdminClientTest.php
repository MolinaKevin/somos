<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Autenticado puede ver la lista de clientes
it('authenticated user can see clients list', function () {
    // Crear un usuario y autenticarlo
    $user = User::factory()->create();

    // Crear algunos clientes
    User::factory()->count(3)->create([
        'name' => 'Test Client'
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($user);

    // Acceder a la página de administrar clientes
    $response = $this->get('/admin/clients');

    // Verificar que la página carga correctamente
    $response->assertStatus(200);

    // Verificar que el texto "Administrar clientes" aparece en la página
    $response->assertSee('Administrar clientes');

    // Verificar que los clientes están en la página
    $response->assertSee('Test Client');
});

// Test: Crear Cliente
it('can create a client', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos del cliente
    $clientData = [
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
        'password' => 'password', 
        'password_confirmation' => 'password', // Confirmación de contraseña correcta
    ];

    // Enviar petición para crear un cliente
    $response = $this->post('/admin/clients', $clientData);

    // Verificar que la respuesta sea un redirect a la lista de clientes
    $response->assertStatus(302);
    $response->assertRedirect('/admin/clients');

    // Verificar que el cliente fue creado en la base de datos
    $this->assertDatabaseHas('users', [
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);
});


// Test: Ver listado de clientes
it('can see the clients list', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear algunos clientes
    User::factory()->count(3)->create([
        'name' => 'Test Client',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de lista de clientes
    $response = $this->get('/admin/clients');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que el listado de clientes está presente
    User::all()->each(function ($client) use ($response) {
        $response->assertSee($client->name);
    });
});


// Test: Ver detalle de cliente
it('can view a client detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear un cliente
    $client = User::factory()->create([
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle del cliente
    $response = $this->get("/admin/clients/{$client->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles del cliente están presentes
    $response->assertSee($client->name);
    $response->assertSee($client->email);
});

it('can update a client', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin);
    // Crear un cliente
    $client = User::factory()->create([
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);

    // Datos actualizados
    $updatedData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ];

    // Enviar la solicitud para actualizar el cliente
    $response = $this->actingAs($client)->put("/admin/clients/{$client->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de clientes
    $response->assertStatus(302);
    $response->assertRedirect('/admin/clients');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('users', [
        'id' => $client->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

// Test: Eliminar Cliente
it('can delete a client', function () {
    // Crear un cliente
    $client = User::factory()->create([
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    // Actuar como el usuario autenticado (por ejemplo, un administrador)
    $response = $this->actingAs($adminUser)->delete("/admin/clients/{$client->id}");

    // Verificar que la respuesta sea un redirect a la lista de clientes
    $response->assertStatus(302);
    $response->assertRedirect('/admin/clients');

    // Verificar que el cliente fue eliminado de la base de datos
    $this->assertDatabaseMissing('users', [
        'id' => $client->id,
    ]);
});

it('validates client data', function () {
    // Crear un usuario administrador o autenticado
    $adminUser = User::factory()->create();

    // Autenticar al usuario
    $this->actingAs($adminUser);

    // Intentar crear un cliente sin datos válidos usando una solicitud JSON
    $response = $this->postJson('/admin/clients', []);

    // Verificar que la respuesta sea un error 422 (Unprocessable Entity)
    $response->assertStatus(422);

    // Verificar que se muestran los mensajes de validación
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});

// Test: Autenticado puede ver la página de crear cliente
it('can view the create client page', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de crear cliente
    $response = $this->get('/admin/clients/create');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la página contiene el formulario para crear un cliente
    $response->assertSee('Crear Cliente');
    $response->assertSee('Nombre');
    $response->assertSee('Correo electrónico');
    $response->assertSee('Contraseña');
});

// Test: Autenticado puede ver la página de editar cliente
it('can view the edit client page', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear un cliente
    $client = User::factory()->create([
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de editar cliente
    $response = $this->get("/admin/clients/{$client->id}/edit");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la página contiene el formulario para editar un cliente
    $response->assertSee('Editar Cliente');
    $response->assertSee('Nombre');
    $response->assertSee('Correo electrónico');
    $response->assertSee($client->name);  // El nombre del cliente debe estar en el formulario
    $response->assertSee($client->email); // El email del cliente debe estar en el formulario
});

