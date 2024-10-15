<?php

use App\Models\Somos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Autenticado puede ver la lista de somos
it('authenticated user can see somos list', function () {
    // Crear un usuario y autenticarlo
    $user = User::factory()->create();

    // Crear algunos somos
    Somos::factory()->count(3)->create([
        'name' => 'Test Somos'
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($user);

    // Acceder a la página de administrar somos
    $response = $this->get('/admin/somos');

    // Verificar que la página carga correctamente
    $response->assertStatus(200);

    // Verificar que el texto "Administrar Somos" aparece en la página
    $response->assertSee('Administrar Somos');

    // Verificar que los somos están en la página
    $response->assertSee('Test Somos');
});

// Test: Crear Somos
it('can create a somos', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos de somos
    $somosData = [
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
        'city' => 'Göttingen',
        'plz' => '37073',
    ];

    // Enviar petición para crear un somos
    $response = $this->post('/admin/somos', $somosData);

    // Verificar que la respuesta sea un redirect a la lista de somos
    $response->assertStatus(302);
    $response->assertRedirect('/admin/somos');

    // Verificar que somos fue creado en la base de datos
    $this->assertDatabaseHas('somos', [
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);
});

// Test: Ver listado de somos
it('can see the somos list', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear algunos somos
    Somos::factory()->count(3)->create([
        'name' => 'Test Somos',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de lista de somos
    $response = $this->get('/admin/somos');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que el listado de somos está presente
    Somos::all()->each(function ($somos) use ($response) {
        $response->assertSee($somos->name);
    });
});

// Test: Ver detalle de somos
it('can view a somos detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear un somos
    $somos = Somos::factory()->create([
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle del somos
    $response = $this->get("/admin/somos/{$somos->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles del somos están presentes
    $response->assertSee($somos->name);
    $response->assertSee($somos->email);
});

// Test: Actualizar Somos
it('can update a somos', function () {
    // Crear un usuario administrador
    $admin = User::factory()->create();

    // Crear un somos
    $somos = Somos::factory()->create([
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);
    
    // Datos actualizados
    $updatedData = [
        'name' => 'Updated Somos',
        'email' => 'updatedsomos@example.com',
    ];

    // Autenticar el usuario administrador
    $this->actingAs($admin);

    // Enviar la solicitud para actualizar el somos
    $response = $this->put("/admin/somos/{$somos->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de somos
    $response->assertStatus(302);
    $response->assertRedirect('/admin/somos');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('somos', [
        'id' => $somos->id,
        'name' => 'Updated Somos',
        'email' => 'updatedsomos@example.com',
    ]);
});

// Test: Eliminar Somos
it('can delete a somos', function () {
    // Crear un somos
    $somos = Somos::factory()->create([
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    // Actuar como el usuario autenticado (por ejemplo, un administrador)
    $response = $this->actingAs($adminUser)->delete("/admin/somos/{$somos->id}");

    // Verificar que la respuesta sea un redirect a la lista de somos
    $response->assertStatus(302);
    $response->assertRedirect('/admin/somos');

    // Verificar que el somos fue eliminado de la base de datos
    $this->assertDatabaseMissing('somos', [
        'id' => $somos->id,
    ]);
});

// Test: Validar datos de Somos
it('validates somos data', function () {
    // Crear un usuario administrador o autenticado
    $adminUser = User::factory()->create();

    // Autenticar al usuario
    $this->actingAs($adminUser);

    // Intentar crear un somos sin datos válidos usando una solicitud JSON
    $response = $this->postJson('/admin/somos', []);

    // Verificar que la respuesta sea un error 422 (Unprocessable Entity)
    $response->assertStatus(422);

    // Verificar que se muestran los mensajes de validación
    $response->assertJsonValidationErrors(['name', 'email']);
});

// Test: Autenticado puede ver la página de crear somos
it('can view the create somos page', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de crear somos
    $response = $this->get('/admin/somos/create');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la página contiene el formulario para crear un somos
    $response->assertSee('Crear Somos');
    $response->assertSee('Nombre');
    $response->assertSee('Correo Electrónico');
    $response->assertSee('Contraseña');
});

// Test: Autenticado puede ver la página de editar somos
it('can view the edit somos page', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();

    // Crear un somos
    $somos = Somos::factory()->create([
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de editar somos
    $response = $this->get("/admin/somos/{$somos->id}/edit");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la página contiene el formulario para editar un somos
    $response->assertSee('Editar Somos');
    $response->assertSee('Nombre');
    $response->assertSee('Correo Electrónico');
    $response->assertSee($somos->name);  // El nombre del somos debe estar en el formulario
    $response->assertSee($somos->email); // El email del somos debe estar en el formulario
});

