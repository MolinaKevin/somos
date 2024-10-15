<?php

use App\Models\Foto;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('fetches commerces and nros with photos', function () {
    // Crear un usuario e iniciar sesión
    $user = User::factory()->create();
    $this->actingAs($user);

    // Crear comercios e instituciones (NROs)
    $commerce1 = Commerce::factory()->create(['name' => 'Comercio 1']);
    $commerce2 = Commerce::factory()->create(['name' => 'Comercio 2']);
    $nro1 = Nro::factory()->create(['name' => 'Institución 1']);

    // Crear fotos asociadas
    $foto1 = Foto::factory()->create(['fotable_id' => $commerce1->id, 'fotable_type' => 'App\Models\Commerce']);
    $foto2 = Foto::factory()->create(['fotable_id' => $nro1->id, 'fotable_type' => 'App\Models\Nro']);

    // Acceder a la acción que obtendrá las fotos
    $response = $this->get('/admin/fotos');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que las fotos y sus respectivos comercios/nros estén presentes
    $response->assertSee($commerce1->name);
    $response->assertSee($foto1->path);
    $response->assertSee($nro1->name);
    $response->assertSee($foto2->path);
});

// Test: Autenticado puede ver la lista de fotos
it('shows all photos when authenticated', function () {
    // Crear un usuario e iniciar sesión
    $user = User::factory()->create();

    // Autenticar al usuario
    $this->actingAs($user);

    // Crear comercios e instituciones y asociar fotos
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();
    
    Foto::factory()->create(['fotable_id' => $commerce->id, 'fotable_type' => 'App\Models\Commerce']);
    Foto::factory()->create(['fotable_id' => $nro->id, 'fotable_type' => 'App\Models\Nro']);
    
    // Acceder a la página de administrar fotos
    $response = $this->get('/admin/fotos');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que el botón "Mostrar todas las fotos" está presente
    $response->assertSee('Mostrar todas las fotos');

    // Verificar que las fotos de cada institución están presentes
    $response->assertSee($commerce->name); // Asegúrate de que el nombre esté presente
    $response->assertSee($nro->name); // Asegúrate de que el nombre esté presente
});


it('shows photos organized by institution in accordion', function () {
    // Crear un usuario e iniciar sesión
    $user = User::factory()->create();

    // Autenticar al usuario
    $this->actingAs($user);

    // Crear comercios e instituciones y asociar fotos
    $commerce = Commerce::factory()->create(['name' => 'Comercio 1']);
    $nro = Nro::factory()->create(['name' => 'Institución 1']);

    // Crear fotos asociadas a comercio e institución
    $foto1 = Foto::factory()->create(['fotable_id' => $commerce->id, 'fotable_type' => 'App\Models\Commerce']);
    $foto2 = Foto::factory()->create(['fotable_id' => $commerce->id, 'fotable_type' => 'App\Models\Commerce']);
    $foto3 = Foto::factory()->create(['fotable_id' => $nro->id, 'fotable_type' => 'App\Models\Nro']);

    // Acceder a la página de administrar fotos
    $response = $this->get('/admin/fotos');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que las fotos de comercio están presentes en el acordeón
    $response->assertSee($commerce->name); // Verifica que el nombre del comercio esté presente
    $response->assertSee($foto1->path); // Verifica que la primera foto esté presente
    $response->assertSee($foto2->path); // Verifica que la segunda foto esté presente

    // Verificar que las fotos de la institución están presentes en el acordeón
    $response->assertSee($nro->name); // Verifica que el nombre de la institución esté presente
    $response->assertSee($foto3->path); // Verifica que la foto de la institución esté presente
});

// Test: Crear Foto
it('can create a foto', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos de la foto
    $fotoData = [
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/new_image.jpg', // Ajustar el valor si es necesario
    ];

    // Enviar petición para crear una foto
    $response = $this->post('/admin/fotos', $fotoData);

    // Verificar que la respuesta sea un redirect a la lista de fotos
    $response->assertStatus(302);
    $response->assertRedirect('/admin/fotos');

    // Verificar que la foto fue creada en la base de datos
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $commerce->id,
        'path' => 'path/to/new_image.jpg', // Ajustar el valor si es necesario
    ]);
});

// Test: Ver detalle de foto
it('can view a foto detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    
    // Crear un comercio y una foto
    $commerce = Commerce::factory()->create();
    $foto = Foto::factory()->create([
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/existing_image.jpg', // Ajustar el valor si es necesario
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle de la foto
    $response = $this->get("/admin/fotos/{$foto->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles de la foto están presentes
    $response->assertSee('path/to/existing_image.jpg'); // Asegúrate de que el formato sea el correcto
});

// Test: Actualizar Foto
it('can update a foto', function () {
    // Crear un usuario administrador
    $admin = User::factory()->create();
    
    // Crear un comercio y una foto
    $commerce = Commerce::factory()->create();
    $foto = Foto::factory()->create([
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/old_image.jpg', // Ajustar el valor si es necesario
    ]);

    // Datos actualizados para la foto
    $updatedData = [
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/updated_image.jpg', // Ajustar el valor si es necesario
    ];

    // Autenticar al usuario administrador
    $this->actingAs($admin);

    // Enviar la solicitud para actualizar la foto
    $response = $this->put("/admin/fotos/{$foto->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de fotos
    $response->assertStatus(302);
    $response->assertRedirect('/admin/fotos');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('fotos', [
        'id' => $foto->id,
        'fotable_id' => $commerce->id,
        'path' => 'path/to/updated_image.jpg', // Ajustar el valor si es necesario
    ]);
});

// Test: Eliminar Foto
it('can delete a foto', function () {
    // Crear un comercio y una foto
    $commerce = Commerce::factory()->create();
    $foto = Foto::factory()->create([
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/deletable_image.jpg', // Ajustar el valor si es necesario
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create();

    // Actuar como el usuario autenticado
    $response = $this->actingAs($adminUser)->delete("/admin/fotos/{$foto->id}");

    // Verificar que la respuesta sea un redirect a la lista de fotos
    $response->assertStatus(302);
    $response->assertRedirect('/admin/fotos');

    // Verificar que la foto fue eliminada de la base de datos
    $this->assertDatabaseMissing('fotos', [
        'id' => $foto->id,
    ]);
});

