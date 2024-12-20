<?php

use App\Models\Foto;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('fetches commerces and nros with photos', function () {
    
    $user = User::factory()->create();
    $this->actingAs($user);

    
    $commerce1 = Commerce::factory()->create(['name' => 'Comercio 1']);
    $commerce2 = Commerce::factory()->create(['name' => 'Comercio 2']);
    $nro1 = Nro::factory()->create(['name' => 'Institución 1']);

    
    $foto1 = Foto::factory()->create(['fotable_id' => $commerce1->id, 'fotable_type' => 'App\Models\Commerce']);
    $foto2 = Foto::factory()->create(['fotable_id' => $nro1->id, 'fotable_type' => 'App\Models\Nro']);

    
    $response = $this->get('/admin/fotos');

    
    $response->assertStatus(200);

    
    $response->assertSee($commerce1->name);
    $response->assertSee($nro1->name);
});


it('shows all photos when authenticated', function () {
    
    $user = User::factory()->create();

    
    $this->actingAs($user);

    
    $commerce = Commerce::factory()->create();
    $nro = Nro::factory()->create();
    
    Foto::factory()->create(['fotable_id' => $commerce->id, 'fotable_type' => 'App\Models\Commerce']);
    Foto::factory()->create(['fotable_id' => $nro->id, 'fotable_type' => 'App\Models\Nro']);
    
    
    $response = $this->get('/admin/fotos');

    
    $response->assertStatus(200);

    
    $response->assertSee($commerce->name); 
    $response->assertSee($nro->name); 
});


it('shows photos organized by institution in accordion', function () {
    
    $user = User::factory()->create();

    
    $this->actingAs($user);

    
    $commerce = Commerce::factory()->create(['name' => 'Comercio 1']);
    $nro = Nro::factory()->create(['name' => 'Institución 1']);

    
    $foto1 = Foto::factory()->create(['fotable_id' => $commerce->id, 'fotable_type' => 'App\Models\Commerce']);
    $foto2 = Foto::factory()->create(['fotable_id' => $commerce->id, 'fotable_type' => 'App\Models\Commerce']);
    $foto3 = Foto::factory()->create(['fotable_id' => $nro->id, 'fotable_type' => 'App\Models\Nro']);

    
    $response = $this->get('/admin/fotos');

    
    $response->assertStatus(200);

    
    $response->assertSee($commerce->name); 

    
    $response->assertSee($nro->name); 
});


it('can create a foto', function () {
    
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $this->actingAs($admin);

    
    $fotoData = [
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/new_image.jpg', 
    ];

    
    $response = $this->post('/admin/fotos', $fotoData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/fotos');

    
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $commerce->id,
        'path' => 'path/to/new_image.jpg', 
    ]);
});


it('can view a foto detail', function () {
    
    $admin = User::factory()->create();
    
    
    $commerce = Commerce::factory()->create();
    $foto = Foto::factory()->create([
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/existing_image.jpg', 
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/fotos/{$foto->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee('path/to/existing_image.jpg'); 
});


it('can update a foto', function () {
    
    $admin = User::factory()->create();
    
    
    $commerce = Commerce::factory()->create();
    $foto = Foto::factory()->create([
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/old_image.jpg', 
    ]);

    
    $updatedData = [
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/updated_image.jpg', 
    ];

    
    $this->actingAs($admin);

    
    $response = $this->put("/admin/fotos/{$foto->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/fotos');

    
    $this->assertDatabaseHas('fotos', [
        'id' => $foto->id,
        'fotable_id' => $commerce->id,
        'path' => 'path/to/updated_image.jpg', 
    ]);
});


it('can delete a foto', function () {
    
    $commerce = Commerce::factory()->create();
    $foto = Foto::factory()->create([
        'fotable_id' => $commerce->id,
        'fotable_type' => Commerce::class,
        'path' => 'path/to/deletable_image.jpg', 
    ]);

    
    $adminUser = User::factory()->create();

    
    $response = $this->actingAs($adminUser)->delete("/admin/fotos/{$foto->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/fotos');

    
    $this->assertDatabaseMissing('fotos', [
        'id' => $foto->id,
    ]);
});

