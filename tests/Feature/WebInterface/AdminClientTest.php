<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('authenticated user can see clients list', function () {
    
    $user = User::factory()->create();

    
    User::factory()->count(3)->create([
        'name' => 'Test Client'
    ]);

    
    $this->actingAs($user);

    
    $response = $this->get('/admin/clients');

    
    $response->assertStatus(200);

    
    $response->assertSee('Administrar clientes');

    
    $response->assertSee('Test Client');
});


it('can create a client', function () {
    
    $admin = User::factory()->create();

    
    $this->actingAs($admin);

    
    $clientData = [
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
        'password' => 'password', 
        'password_confirmation' => 'password', 
    ];

    
    $response = $this->post('/admin/clients', $clientData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/clients');

    
    $this->assertDatabaseHas('users', [
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);
});



it('can see the clients list', function () {
    
    $admin = User::factory()->create();

    
    User::factory()->count(3)->create([
        'name' => 'Test Client',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get('/admin/clients');

    
    $response->assertStatus(200);

    
    User::all()->each(function ($client) use ($response) {
        $response->assertSee($client->name);
    });
});



it('can view a client detail', function () {
    
    $admin = User::factory()->create();

    
    $client = User::factory()->create([
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/clients/{$client->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee($client->name);
    $response->assertSee($client->email);
});

it('can update a client', function () {
    $admin = User::factory()->create();

    $this->actingAs($admin);
    
    $client = User::factory()->create([
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);

    
    $updatedData = [
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ];

    
    $response = $this->actingAs($client)->put("/admin/clients/{$client->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/clients');

    
    $this->assertDatabaseHas('users', [
        'id' => $client->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});


it('can delete a client', function () {
    
    $client = User::factory()->create([
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);

    
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    
    $response = $this->actingAs($adminUser)->delete("/admin/clients/{$client->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/clients');

    
    $this->assertDatabaseMissing('users', [
        'id' => $client->id,
    ]);
});

it('validates client data', function () {
    
    $adminUser = User::factory()->create();

    
    $this->actingAs($adminUser);

    
    $response = $this->postJson('/admin/clients', []);

    
    $response->assertStatus(422);

    
    $response->assertJsonValidationErrors(['name', 'email', 'password']);
});


it('can view the create client page', function () {
    
    $admin = User::factory()->create();

    
    $this->actingAs($admin);

    
    $response = $this->get('/admin/clients/create');

    
    $response->assertStatus(200);

    
    $response->assertSee('Crear Cliente');
    $response->assertSee('Nombre');
    $response->assertSee('Correo electrónico');
    $response->assertSee('Contraseña');
});


it('can view the edit client page', function () {
    
    $admin = User::factory()->create();

    
    $client = User::factory()->create([
        'name' => 'Test Client',
        'email' => 'testclient@example.com',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/clients/{$client->id}/edit");

    
    $response->assertStatus(200);

    
    $response->assertSee('Editar Cliente');
    $response->assertSee('Nombre');
    $response->assertSee('Correo electrónico');
    $response->assertSee($client->name);  
    $response->assertSee($client->email); 
});

