<?php

use App\Models\Nro;
use App\Models\User;
use App\Models\Somos;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('authenticated user can see nros list', function () {
    
    $user = User::factory()->create();

    
    Nro::factory()->count(3)->create([
        'name' => 'Test Institution'
    ]);

    
    $this->actingAs($user);

    
    $response = $this->get('/admin/nros');

    
    $response->assertStatus(200);

    
    $response->assertSee('Administrar Instituciones');

    
    $response->assertSee('Test Institution');
});


it('can create an institution', function () {
    
    $admin = User::factory()->create();

    
    $this->actingAs($admin);

    
    $somos = Somos::factory()->create();

    
    $nroData = [
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
        'somos_id' => $somos->id
    ];

    
    $response = $this->post('/admin/nros', $nroData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/nros');

    
    $this->assertDatabaseHas('nros', [
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);
});


it('can see the nros list', function () {
    
    $admin = User::factory()->create();

    
    Nro::factory()->count(3)->create([
        'name' => 'Test Institution',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get('/admin/nros');

    
    $response->assertStatus(200);

    
    Nro::all()->each(function ($nro) use ($response) {
        $response->assertSee($nro->name);
    });
});


it('can view an institution detail', function () {
    
    $admin = User::factory()->create();

    
    $nro = Nro::factory()->create([
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/nros/{$nro->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee($nro->name);
    $response->assertSee($nro->email);
});

it('can update an institution', function () {
    
    $admin = User::factory()->create();

    
    $nro = Nro::factory()->create([
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);

    
    $updatedData = [
        'name' => 'Updated Institution',
        'email' => 'updatedinstitution@example.com',
    ];

    
    $this->actingAs($admin);

    
    $response = $this->put("/admin/nros/{$nro->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/nros');

    
    $this->assertDatabaseHas('nros', [
        'id' => $nro->id,
        'name' => 'Updated Institution',
        'email' => 'updatedinstitution@example.com',
    ]);
});


it('can delete an institution', function () {
    
    $nro = Nro::factory()->create([
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);

    
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    
    $response = $this->actingAs($adminUser)->delete("/admin/nros/{$nro->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/nros');

    
    $this->assertDatabaseMissing('nros', [
        'id' => $nro->id,
    ]);
});

it('validates institution data', function () {
    
    $adminUser = User::factory()->create();

    
    $this->actingAs($adminUser);

    
    $response = $this->postJson('/admin/nros', []);

    
    $response->assertStatus(422);

    
    $response->assertJsonValidationErrors(['name', 'email']);
});


it('can view the create institution page', function () {
    
    $admin = User::factory()->create();

    
    $this->actingAs($admin);

    
    $response = $this->get('/admin/nros/create');

    
    $response->assertStatus(200);

    
    $response->assertSee('Crear Institución');
    $response->assertSee('Nombre');
    $response->assertSee('Correo Electrónico');
    $response->assertSee('Contraseña');
});


it('can view the edit institution page', function () {
    
    $admin = User::factory()->create();

    
    $nro = Nro::factory()->create([
        'name' => 'Test Institution',
        'email' => 'testinstitution@example.com',
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/nros/{$nro->id}/edit");

    
    $response->assertStatus(200);

    
    $response->assertSee('Editar Institución');
    $response->assertSee('Nombre');
    $response->assertSee('Correo Electrónico');
    $response->assertSee($nro->name);
    $response->assertSee($nro->email);
});

