<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\Foto;

beforeEach(function () {
    $this->artisan('migrate:fresh'); 
    Storage::fake('public'); 
});

it('allows a commerce to upload a foto', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $foto = UploadedFile::fake()->image('commerce-foto.jpg');

    
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-image", [
        'foto' => $foto,  
    ]);

    
    $response->assertStatus(200);

    
    $friendlyPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();

    
    Storage::disk('public')->assertExists($friendlyPath);

    
    $response->assertJson([
        'url' => Storage::url($friendlyPath),
    ]);
});


it('allows an nro (institution) to upload a foto', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();

    
    $foto = UploadedFile::fake()->image('nro-foto.jpg');

    
    $response = $this->postJson("/api/nros/{$nro->id}/upload-image", [
        'foto' => $foto,
    ]);

    
    $response->assertStatus(200);

    
    $friendlyPath = "fotos/nros/{$nro->id}/" . $foto->hashName();

    
    Storage::disk('public')->assertExists($friendlyPath);

    
    $response->assertJson([
        'url' => Storage::url($friendlyPath),
    ]);
});


it('allows a commerce to upload a foto and associates it', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $foto = UploadedFile::fake()->image('commerce-foto.jpg');

    
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-image", [
        'foto' => $foto,
    ]);

    
    $response->assertStatus(200);

    
    $friendlyPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();

    
    Storage::disk('public')->assertExists($friendlyPath);

    
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $commerce->id,
        'fotable_type' => \App\Models\Commerce::class,
        'path' => $friendlyPath,
    ]);

    
    $response->assertJson([
        'url' => Storage::url($friendlyPath),
    ]);
});


it('creates a foto and associates it with a commerce', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $foto = UploadedFile::fake()->image('commerce-foto.jpg');

    
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-image", [
        'foto' => $foto,
    ]);

    
    $response->assertStatus(200);

    
    $friendlyPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();

    
    Storage::disk('public')->assertExists($friendlyPath);

    
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $commerce->id,
        'fotable_type' => \App\Models\Commerce::class,
        'path' => $friendlyPath,
    ]);

    
    $this->assertCount(1, Foto::where('fotable_id', $commerce->id)->get());
});

it('creates a foto and associates it with an nro', function () {
    
    Foto::query()->delete();

    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();

    
    $foto = UploadedFile::fake()->image('nro-foto.jpg');

    
    $response = $this->postJson("/api/nros/{$nro->id}/upload-image", [
        'foto' => $foto,
    ]);

    
    $response->assertStatus(200);

    
    $friendlyPath = "fotos/nros/{$nro->id}/" . $foto->hashName();

    
    Storage::disk('public')->assertExists($friendlyPath);

    
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $nro->id,
        'fotable_type' => \App\Models\Nro::class,
        'path' => $friendlyPath,
    ]);

    
    $this->assertCount(1, Foto::where('fotable_id', $nro->id)->get());
});


it('ensures friendly paths for uploaded fotos', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $foto = UploadedFile::fake()->image('commerce-foto.jpg');

    
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-image", [
        'foto' => $foto,
    ]);

    
    $response->assertStatus(200);

    
    $friendlyPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();

    
    Storage::disk('public')->assertExists($friendlyPath);

    
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $commerce->id,
        'fotable_type' => \App\Models\Commerce::class,
        'path' => $friendlyPath,
    ]);
});

