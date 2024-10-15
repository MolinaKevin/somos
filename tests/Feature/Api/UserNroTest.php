<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Somos;
use App\Models\Nro;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('verifies that a nro appears in the users list of nros', function () {
    $user = User::factory()->create();
    $data = [
        'name' => 'Test Nro',
        'description' => 'Test Description',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'nro@example.com',
    ];

    $nro = Nro::factory()->create($data);
    $nro->users()->attach($user->id);
    $userNros = $user->nros;
    $this->assertTrue($userNros->contains($nro));
    $this->assertCount(1, $userNros);
});

it('verifies that a nro appears in the user\'s list of nros via API', function () {
    $user = User::factory()->create();
    $data = [
        'name' => 'Test Nro',
        'description' => 'Test Description',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'nro@example.com',
    ];

    $nro = Nro::factory()->create($data);
    $nro->users()->attach($user->id);
    Sanctum::actingAs($user, ['*']);
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $nro->id]);
    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
});

it('verifies that creating a nro', function () {
    $data = [
        'name' => 'Test Nro',
        'description' => 'Test Description',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'nro@example.com',
    ];

    $nro = Nro::factory()->create($data);
    $this->assertNotNull($nro);
    $this->assertEquals('Test Nro', $nro->name);
    $this->assertEquals('123 Street', $nro->address);
    $this->assertEquals('City', $nro->city);
    $this->assertEquals('12345', $nro->plz);
    $this->assertEquals('nro@example.com', $nro->email);
});

it('returns all associated nros for the authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);
    $nros = collect(range(1, 3))->map(function ($i) use ($user) {
        $data = [
            'name' => "Test Nro {$i}",
            'description' => "Description {$i}",
            'address' => "123 Street {$i}",
            'city' => "City {$i}",
            'plz' => "1234{$i}",
            'email' => "nro{$i}@example.com",
        ];
        $nro = Nro::factory()->create($data);
        $response = $this->postJson("/api/nros/{$nro->id}/associate", ['user_id' => $user->id]);
        $response->assertStatus(200);
        return $nro;
    });

    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);

    foreach ($nros as $nro) {
        $response->assertJsonFragment(['id' => $nro->id]);
    }
});

it('allows a user to have multiple associated nros', function () {
    $user = User::factory()->create();
    $data1 = [
        'name' => 'First Nro',
        'description' => 'Description 1',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'first@example.com',
    ];
    $nro1 = Nro::factory()->create($data1);

    $data2 = [
        'name' => 'Second Nro',
        'description' => 'Description 2',
        'address' => '456 Street',
        'city' => 'Another City',
        'plz' => '67890',
        'email' => 'second@example.com',
    ];
    $nro2 = Nro::factory()->create($data2);

    $nro1->users()->attach($user->id);
    $nro2->users()->attach($user->id);
    $userNros = $user->nros;
    $this->assertTrue($userNros->contains($nro1));
    $this->assertTrue($userNros->contains($nro2));
    $this->assertCount(2, $userNros);
});

it('allows a user to have multiple associated nros via API', function () {
    $user = User::factory()->create();
    $data1 = [
        'name' => 'First Nro',
        'description' => 'Description 1',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'first@example.com',
    ];
    $nro1 = Nro::factory()->create($data1);

    $data2 = [
        'name' => 'Second Nro',
        'description' => 'Description 2',
        'address' => '456 Street',
        'city' => 'Another City',
        'plz' => '67890',
        'email' => 'second@example.com',
    ];
    $nro2 = Nro::factory()->create($data2);

    Sanctum::actingAs($user, ['*']);
    $this->postJson("/api/nros/{$nro1->id}/associate", ['user_id' => $user->id])->assertStatus(200);
    $this->postJson("/api/nros/{$nro2->id}/associate", ['user_id' => $user->id])->assertStatus(200);

    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);

    $nroIds = array_column($responseData, 'id');
    $this->assertContains($nro1->id, $nroIds);
    $this->assertContains($nro2->id, $nroIds);
});

it('allows a user to associate their nro with another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $data = [
        'name' => 'Test Nro',
        'description' => 'Test Description',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'nro@example.com',
    ];
    $nro = Nro::factory()->create($data);
    $nro->users()->attach($user1->id);

    $this->assertDatabaseHas('nro_user', [
        'user_id' => $user1->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user2, ['*']);
    $this->postJson("/api/nros/{$nro->id}/associate", ['user_id' => $user2->id])->assertStatus(200);

    $this->assertDatabaseHas('nro_user', [
        'user_id' => $user2->id,
        'nro_id' => $nro->id,
    ]);
    $this->assertDatabaseHas('nro_user', [
        'user_id' => $user1->id,
        'nro_id' => $nro->id,
    ]);
});

it('associates nros to users and verifies visibility based on user context', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $data1 = [
        'name' => 'Test Nro 1',
        'description' => 'Description 1',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'nro1@example.com',
    ];
    $nro1 = Nro::factory()->create($data1);

    $data2 = [
        'name' => 'Test Nro 2',
        'description' => 'Description 2',
        'address' => '456 Street',
        'city' => 'Another City',
        'plz' => '67890',
        'email' => 'nro2@example.com',
    ];
    $nro2 = Nro::factory()->create($data2);

    $nro1->users()->attach($user1->id);
    $nro2->users()->attach($user2->id);

    Sanctum::actingAs($user1, ['*']);
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($nro1->id, $responseData[0]['id']);
    $this->assertNotEquals($nro2->id, $responseData[0]['id']);

    Sanctum::actingAs($user2, ['*']);
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($nro2->id, $responseData[0]['id']);
    $this->assertNotEquals($nro1->id, $responseData[0]['id']);

    Sanctum::actingAs($user2, ['*']);
    $this->postJson("/api/nros/{$nro2->id}/associate", ['user_id' => $user1->id])->assertStatus(200);

    Sanctum::actingAs($user1, ['*']);
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);

    $nroIds = array_column($responseData, 'id');
    $this->assertContains($nro1->id, $nroIds);
    $this->assertContains($nro2->id, $nroIds);
});

it('verifies that a newly created nro has the active attribute set to false', function () {
    $data = [
        'name' => 'Test Nro',
        'description' => 'Test Description',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'nro@example.com',
    ];

    $nro = Nro::factory()->create($data);
    $this->assertFalse($nro->active);
});

it('verifies that a newly created nro has the accepted attribute set to false', function () {
    $data = [
        'name' => 'Test Nro',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'nro@example.com',
    ];

    $nro = Nro::factory()->create($data);
    $this->assertFalse($nro->accepted);
});

it('deactivates a nro when it is unaccepted', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create([
        'active' => true,
        'accepted' => true,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/nros/{$nro->id}/unaccept");
    $response->assertStatus(200);
    $this->assertFalse($nro->fresh()->accepted);
    $this->assertFalse($nro->fresh()->active);
});

it('authenticated user can create nro', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear un Somos
    $somos = Somos::factory()->create();

    // Datos para crear el nro
    $data = [
        'somos_id' => $somos->id,
        'name' => 'Test Nro',
        'description' => 'Descripción del nro',
        'address' => '123 Calle',
        'city' => 'Ciudad',
        'plz' => '12345',
        'email' => 'nro@example.com',
        'phone_number' => '+1-123-456-7890',
        'website' => 'https://nro.example.com',
        'opening_time' => '09:00',
        'closing_time' => '18:00',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'percent' => 10.5,
    ];

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Enviar la solicitud para crear el nro
    $response = $this->postJson('/api/user/nros', $data);

    // Verificar que la respuesta tenga el código de estado 201 (Creado)
    $response->assertStatus(201);

    // Verificar que el nro existe en la base de datos
    $this->assertDatabaseHas('nros', [
        'name' => 'Test Nro',
        'email' => 'nro@example.com',
    ]);
});

it('authenticated user can get nro list', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear varios Nro y asociarlos al usuario
    $nros = Nro::factory()->count(3)->create()->each(function ($nro) use ($user) {
        $nro->users()->attach($user->id);
    });

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Enviar la solicitud para obtener la lista de Nros del usuario autenticado
    $response = $this->getJson('/api/user/nros');

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que se retornan 3 Nros
    $response->assertJsonCount(3, 'data');
});

it('authenticated user can get specific nro', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear un nro y asociarlo al usuario
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Enviar la solicitud para obtener un nro específico
    $response = $this->getJson("/api/user/nros/{$nro->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que la respuesta contiene los datos del nro
    $response->assertJsonFragment([
        'id' => $nro->id,
        'name' => $nro->name,
    ]);
});

it('authenticated user can update nro', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear un nro y asociarlo al usuario
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    // Datos para actualizar el nro
    $data = ['name' => 'Updated Nro Name'];

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Enviar la solicitud para actualizar el nro
    $response = $this->putJson("/api/user/nros/{$nro->id}", $data);

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(200);

    // Verificar que el nro fue actualizado
    $this->assertDatabaseHas('nros', [
        'id' => $nro->id,
        'name' => 'Updated Nro Name',
    ]);
});

it('authenticated user can delete nro', function () {
    // Crear un usuario
    $user = User::factory()->create();

    // Crear un nro y asociarlo al usuario
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    // Autenticar al usuario
    Sanctum::actingAs($user, ['*']);

    // Enviar la solicitud para eliminar el nro
    $response = $this->deleteJson("/api/user/nros/{$nro->id}");

    // Verificar que la respuesta sea exitosa
    $response->assertStatus(204);

    // Verificar que el nro fue eliminado
    $this->assertDatabaseMissing('nros', [
        'id' => $nro->id,
    ]);
});

it('allows activating a nro', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create([
        'active' => false,
        'accepted' => true,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/user/nros/{$nro->id}/activate");
    $response->assertStatus(200);
    $this->assertTrue($nro->fresh()->active);
});

it('allows deactivating a nro', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create([
        'active' => true,
        'accepted' => true,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/user/nros/{$nro->id}/deactivate");
    $response->assertStatus(200);
    $this->assertFalse($nro->fresh()->active);
});

it('allows accepting a nro', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create([
        'accepted' => false,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/nros/{$nro->id}/accept");
    $response->assertStatus(200);
    $this->assertTrue($nro->fresh()->accepted);
});

it('allows unaccepting a nro and deactivates it automatically', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create([
        'active' => true,
        'accepted' => true,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/nros/{$nro->id}/unaccept");
    $response->assertStatus(200);
    $this->assertFalse($nro->fresh()->accepted);
    $this->assertFalse($nro->fresh()->active);
});

