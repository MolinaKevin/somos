<?php

use App\Models\Nro;
use App\Models\User;
use App\Models\Somos;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Autenticado puede ver la lista de instituciones
it('authenticated user can see nros list', function () {
    // Crear un usuario y autenticarlo
    $user = User::factory()->create();

    // Crear algunas instituciones
    Nro::factory()->count(3)->create([
        'name' => 'Test Institution'
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($user);

    // Acceder a la página de administrar instituciones
    $response = $this->get('/admin/nros');

    // Verificar que la página carga correctamente
    $response->assertStatus(200);

    // Verificar que el texto "Administrar Instituciones" aparece en la página
    $response->assertSee('Administrar Instituciones');

    // Verificar que las instituciones están en la página
    $response->assertSee('Test Institution');
});

// Test: Crear Institución
it('can create an institution', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Somos default
    $somos = Somos::factory()->create();

    // Datos de la institución
    $nroData = [
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
        'somos_id' => $somos->id
    ];

    // Enviar petición para crear una institución
    $response = $this->post('/admin/nros', $nroData);

    // Verificar que la respuesta sea un redirect a la lista de instituciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/nros');

    // Verificar que la institución fue creada en la base de datos
    $this->assertDatabaseHas('nros', [
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);
});

// Test: Ver listado de instituciones
it('can see the nros list', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear algunas instituciones
    Nro::factory()->count(3)->create([
        'name' => 'Test Institution',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de lista de instituciones
    $response = $this->get('/admin/nros');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que el listado de instituciones está presente
    Nro::all()->each(function ($nro) use ($response) {
        $response->assertSee($nro->name);
    });
});

// Test: Ver detalle de institución
it('can view an institution detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear una institución
    $nro = Nro::factory()->create([
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle de la institución
    $response = $this->get("/admin/nros/{$nro->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles de la institución están presentes
    $response->assertSee($nro->name);
    $response->assertSee($nro->email);
});

it('can update an institution', function () {
    // Crear un usuario administrador
    $admin = User::factory()->create();

    // Crear una institución
    $nro = Nro::factory()->create([
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);

    // Datos actualizados
    $updatedData = [
        'name' => 'Updated Institution',
        'email' => 'updatedinstitution@example.com',
    ];

    // Autenticar el usuario administrador
    $this->actingAs($admin);

    // Enviar la solicitud para actualizar la institución
    $response = $this->put("/admin/nros/{$nro->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de instituciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/nros');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('nros', [
        'id' => $nro->id,
        'name' => 'Updated Institution',
        'email' => 'updatedinstitution@example.com',
    ]);
});

// Test: Eliminar Institución
it('can delete an institution', function () {
    // Crear una institución
    $nro = Nro::factory()->create([
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    // Actuar como el usuario autenticado (por ejemplo, un administrador)
    $response = $this->actingAs($adminUser)->delete("/admin/nros/{$nro->id}");

    // Verificar que la respuesta sea un redirect a la lista de instituciones
    $response->assertStatus(302);
    $response->assertRedirect('/admin/nros');

    // Verificar que la institución fue eliminada de la base de datos
    $this->assertDatabaseMissing('nros', [
        'id' => $nro->id,
    ]);
});

it('validates institution data', function () {
    // Crear un usuario administrador o autenticado
    $adminUser = User::factory()->create();

    // Autenticar al usuario
    $this->actingAs($adminUser);

    // Intentar crear una institución sin datos válidos usando una solicitud JSON
    $response = $this->postJson('/admin/nros', []);

    // Verificar que la respuesta sea un error 422 (Unprocessable Entity)
    $response->assertStatus(422);

    // Verificar que se muestran los mensajes de validación
    $response->assertJsonValidationErrors(['name', 'email']);
});

// Test: Autenticado puede ver la página de crear institución
it('can view the create institution page', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de crear institución
    $response = $this->get('/admin/nros/create');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la página contiene el formulario para crear una institución
    $response->assertSee('Crear Institución');
    $response->assertSee('Nombre');
    $response->assertSee('Correo Electrónico');
    $response->assertSee('Contraseña');
});

// Test: Autenticado puede ver la página de editar institución
it('can view the edit institution page', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear una institución
    $nro = Nro::factory()->create([
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de editar institución
    $response = $this->get("/admin/nros/{$nro->id}/edit");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la página contiene el formulario para editar una institución
    $response->assertSee('Editar Institución');
    $response->assertSee('Nombre');
    $response->assertSee('Correo Electrónico');
    $response->assertSee($nro->name);
    $response->assertSee($nro->email);
});

