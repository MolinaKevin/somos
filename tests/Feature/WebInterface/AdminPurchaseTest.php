<?php

use App\Models\Purchase;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Test: Autenticado puede ver la lista de purchases
it('authenticated user can see purchases list', function () {
    // Crear un usuario y autenticarlo
    $user = User::factory()->create();
    $user2 = User::factory()->create();

    // Crear un comercio
    $commerce = Commerce::factory()->create();

    // Crear algunas purchases
    Purchase::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user2->id,
        'amount' => 10000, // Ajustar el valor si es necesario (en centavos)
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($user);

    // Acceder a la página de administrar purchases
    $response = $this->get('/admin/purchases');

    // Verificar que la página carga correctamente
    $response->assertStatus(200);

    // Verificar que las purchases están en la página
    $response->assertSee('100.00'); // Asegúrate de que el formato sea el correcto
});

// Test: Crear Purchase
it('can create a purchase', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $user = User::factory()->create();

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Datos de la purchase
    $purchaseData = [
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'amount' => 20000, // Ajustar el valor si es necesario (en centavos)
    ];

    // Enviar petición para crear una purchase
    $response = $this->post('/admin/purchases', $purchaseData);

    // Verificar que la respuesta sea un redirect a la lista de purchases
    $response->assertStatus(302);
    $response->assertRedirect('/admin/purchases');

    // Verificar que la purchase fue creada en la base de datos
    $this->assertDatabaseHas('purchases', [
        'commerce_id' => $commerce->id,
        'amount' => 20000, // Ajustar el valor si es necesario (en centavos)
    ]);
});

// Test: Ver detalle de purchase
it('can view a purchase detail', function () {
    // Crear un usuario administrador y autenticarlo
    $admin = User::factory()->create();
    $user = User::factory()->create();
    
    // Crear un comercio y una purchase
    $commerce = Commerce::factory()->create();
    $purchase = Purchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'amount' => 20000, // Ajustar el valor si es necesario (en centavos)
    ]);

    // Actuar como el usuario autenticado
    $this->actingAs($admin);

    // Acceder a la página de detalle de la purchase
    $response = $this->get("/admin/purchases/{$purchase->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que los detalles de la purchase están presentes
    $response->assertSee('200.00'); // Asegúrate de que el formato sea el correcto
});

// Test: Actualizar Purchase
it('can update a purchase', function () {
    // Crear un usuario administrador
    $admin = User::factory()->create();

    $user = User::factory()->create();
    // Crear un comercio y una purchase
    $commerce = Commerce::factory()->create();
    $purchase = Purchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'amount' => 10000, // Ajustar el valor si es necesario (en centavos)
    ]);

    // Datos actualizados para la purchase
    $updatedData = [
        'commerce_id' => $commerce->id, 
        'user_id' => $user->id,
        'amount' => 20000, // Ajustar el valor si es necesario (en centavos)
    ];

    // Autenticar al usuario administrador
    $this->actingAs($admin);

    // Enviar la solicitud para actualizar la purchase
    $response = $this->put("/admin/purchases/{$purchase->id}", $updatedData);

    // Verificar que la respuesta sea un redirect a la lista de purchases
    $response->assertStatus(302);
    $response->assertRedirect('/admin/purchases');

    // Verificar que los datos fueron actualizados en la base de datos
    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'commerce_id' => $commerce->id,
        'amount' => 20000, // Ajustar el valor si es necesario (en centavos)
    ]);
});

// Test: Eliminar Purchase
it('can delete a purchase', function () {
    // Crear un comercio y una purchase
    $commerce = Commerce::factory()->create();
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'amount' => 20000, // Ajustar el valor si es necesario (en centavos)
    ]);

    // Autenticar un usuario con permisos para eliminar
    $adminUser = User::factory()->create();

    // Actuar como el usuario autenticado
    $response = $this->actingAs($adminUser)->delete("/admin/purchases/{$purchase->id}");

    // Verificar que la respuesta sea un redirect a la lista de purchases
    $response->assertStatus(302);
    $response->assertRedirect('/admin/purchases');

    // Verificar que la purchase fue eliminada de la base de datos
    $this->assertDatabaseMissing('purchases', [
        'id' => $purchase->id,
    ]);
});

