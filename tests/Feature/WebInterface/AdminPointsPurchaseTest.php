<?php

use App\Models\PointsPurchase;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Autenticado puede ver la lista de /pointsPurchases
it('authenticated user can see points /pointsPurchases list', function () {
    // Crear un usuario y autenticarlo
    $user = User::factory()->create();
    $user2 = User::factory()->create();

    // Crear un comercio
    $commerce = Commerce::factory()->create();

    // Crear algunas /pointsPurchases
    PointsPurchase::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user2->id,
        'points' => 100, // Ajustar el valor si es necesario
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($user);

    // Acceder a la página de administrar /pointsPurchases
    $response = $this->get('/admin/pointsPurchases');

    // Verificar que la página carga correctamente
    $response->assertStatus(200);

    // Verificar que las /pointsPurchases están en la página
    $response->assertSee('100'); // Asegúrate de que el formato sea el correcto
});

// Test: Crear PointsPurchase
it('can create a points purchase', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $user = User::factory()->create(['points' => 300]); // Asegúrate de que el usuario tenga suficientes puntos

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos de la purchase
    $pointsPurchasesData = [
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'points' => 200, // Ajustar el valor si es necesario
    ];

    // Enviar petición para crear una points purchase
    $response = $this->post('/admin/pointsPurchases', $pointsPurchasesData);

    // Verificar que la respuesta sea un redirect a la lista de /pointsPurchases
    $response->assertStatus(302);
    $response->assertRedirect('/admin/pointsPurchases');

    // Verificar que la points purchase fue creada en la base de datos
    $this->assertDatabaseHas('points_purchases', [
        'commerce_id' => $commerce->id,
        'points' => 200, // Ajustar el valor si es necesario
    ]);
});

// Test: Ver detalle de PointsPurchase
it('can view a points purchase detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    $user = User::factory()->create(['points' => 300]); // Asegúrate de que el usuario tenga suficientes puntos
    
    // Crear un comercio y una points purchase
    $commerce = Commerce::factory()->create();
    $pointsPurchases = PointsPurchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'points' => 200, // Ajustar el valor si es necesario
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle de la points purchase
    $response = $this->get("/admin/pointsPurchases/{$pointsPurchases->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles de la points purchase están presentes
    $response->assertSee('200'); // Asegúrate de que el formato sea el correcto
});

// Test: Actualizar PointsPurchase
it('can update a points purchase', function () {
    // Crear un usuario administrador
    $admin = User::factory()->create();

    $user = User::factory()->create(['points' => 300]); // Asegúrate de que el usuario tenga suficientes puntos
    // Crear un comercio y una points purchase
    $commerce = Commerce::factory()->create();
    $pointsPurchases = PointsPurchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'points' => 100, // Ajustar el valor si es necesario
    ]);

    // Datos actualizados para la points purchase
    $updatedData = [
        'commerce_id' => $commerce->id, 
        'user_id' => $user->id,
        'points' => 200, // Ajustar el valor si es necesario
    ];

    // Autenticar al usuario administrador
    $this->actingAs($admin);

    // Enviar la solicitud para actualizar la points purchase
    $response = $this->put("/admin/pointsPurchases/{$pointsPurchases->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de /pointsPurchases
    $response->assertStatus(302);
    $response->assertRedirect('/admin/pointsPurchases');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('points_purchases', [
        'id' => $pointsPurchases->id,
        'commerce_id' => $commerce->id,
        'points' => 200, // Ajustar el valor si es necesario
    ]);
});

// Test: Eliminar PointsPurchase
it('can delete a points purchase', function () {
    // Crear un comercio y una points purchase
    $commerce = Commerce::factory()->create();
    $user = User::factory()->create(['points' => 300]); // Asegúrate de que el usuario tenga suficientes puntos
    $pointsPurchases = PointsPurchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'points' => 200, // Ajustar el valor si es necesario
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create();

    // Actuar como el usuario autenticado
    $response = $this->actingAs($adminUser)->delete("/admin/pointsPurchases/{$pointsPurchases->id}");

    // Verificar que la respuesta sea un redirect a la lista de /pointsPurchases
    $response->assertStatus(302);
    $response->assertRedirect('/admin/pointsPurchases');

    // Verificar que la points purchase fue eliminada de la base de datos
    $this->assertDatabaseMissing('points_purchases', [
        'id' => $pointsPurchases->id,
    ]);
});

