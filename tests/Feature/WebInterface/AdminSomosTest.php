<?php

use App\Models\Somos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('authenticated user can see somos list', function () {
    
    $user = User::factory()->create();

    
    Somos::factory()->count(3)->create([
        'name' => 'Test Somos'
    ]);

    
    $this->actingAs($user);

    
    $response = $this->get('/admin/somos');

    
    $response->assertStatus(200);

    
    $response->assertSee('Administrar Somos');

    
    $response->assertSee('Test Somos');
});


it('can create a somos', function () {
    
    $admin = User::factory()->create();

    
    $this->actingAs($admin);

    
    $somosData = [
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
        'city' => 'Göttingen',
        'plz' => '37073',
    ];

    
    $response = $this->post('/admin/somos', $somosData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/somos');

    
    $this->assertDatabaseHas('somos', [
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);
});


it('can see the somos list', function () {
    
    $admin = User::factory()->create();

    
    Somos::factory()->count(3)->create([
        'name' => 'Test Somos',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get('/admin/somos');

    
    $response->assertStatus(200);

    
    Somos::all()->each(function ($somos) use ($response) {
        $response->assertSee($somos->name);
    });
});


it('can view a somos detail', function () {
    
    $admin = User::factory()->create();

    
    $somos = Somos::factory()->create([
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/somos/{$somos->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee($somos->name);
    $response->assertSee($somos->email);
});


it('can update a somos', function () {
    
    $admin = User::factory()->create();

    
    $somos = Somos::factory()->create([
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);
    
    
    $updatedData = [
        'name' => 'Updated Somos',
        'email' => 'updatedsomos@example.com',
    ];

    
    $this->actingAs($admin);

    
    $response = $this->put("/admin/somos/{$somos->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/somos');

    
    $this->assertDatabaseHas('somos', [
        'id' => $somos->id,
        'name' => 'Updated Somos',
        'email' => 'updatedsomos@example.com',
    ]);
});


it('can delete a somos', function () {
    
    $somos = Somos::factory()->create([
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);

    
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    
    $response = $this->actingAs($adminUser)->delete("/admin/somos/{$somos->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/somos');

    
    $this->assertDatabaseMissing('somos', [
        'id' => $somos->id,
    ]);
});


it('validates somos data', function () {
    
    $adminUser = User::factory()->create();

    
    $this->actingAs($adminUser);

    
    $response = $this->postJson('/admin/somos', []);

    
    $response->assertStatus(422);

    
    $response->assertJsonValidationErrors(['name', 'email']);
});


it('can view the create somos page', function () {
    
    $admin = User::factory()->create();

    
    $this->actingAs($admin);

    
    $response = $this->get('/admin/somos/create');

    
    $response->assertStatus(200);

    
    $response->assertSee('Crear Somos');
    $response->assertSee('Nombre');
    $response->assertSee('Correo Electrónico');
    $response->assertSee('Contraseña');
});


it('can view the edit somos page', function () {
    
    $admin = User::factory()->create();

    
    $somos = Somos::factory()->create([
        'name' => 'Test Somos',
        'email' => 'testsomos@example.com',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/somos/{$somos->id}/edit");

    
    $response->assertStatus(200);

    
    $response->assertSee('Editar Somos');
    $response->assertSee('Nombre');
    $response->assertSee('Correo Electrónico');
    $response->assertSee($somos->name);  
    $response->assertSee($somos->email); 
});

