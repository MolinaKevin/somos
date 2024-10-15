<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Nro;
use App\Models\Foto;

beforeEach(function () {
    $this->artisan('migrate:fresh'); // Limpia la base de datos
    Storage::fake('public'); // Usar un disco falso para simular la subida de archivos
});

it('allows a commerce to upload a foto', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Simular un archivo de imagen
    $foto = UploadedFile::fake()->image('commerce-foto.jpg');

    // Hacer la solicitud de subida de imagen para el comercio
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-image", [
        'foto' => $foto,  // Asegúrate de usar 'foto'
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Generar el path amigable
    $friendlyPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();

    // Verificar que la imagen se haya almacenado en el path correcto
    Storage::disk('public')->assertExists($friendlyPath);

    // Verificar la respuesta JSON contiene la URL de la imagen subida
    $response->assertJson([
        'url' => Storage::url($friendlyPath),
    ]);
});


it('allows an nro (institution) to upload a foto', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO (institución)
    $nro = Nro::factory()->create();

    // Simular un archivo de imagen
    $foto = UploadedFile::fake()->image('nro-foto.jpg');

    // Hacer la solicitud de subida de imagen para la NRO
    $response = $this->postJson("/api/nros/{$nro->id}/upload-image", [
        'foto' => $foto,
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Generar el path amigable
    $friendlyPath = "fotos/nros/{$nro->id}/" . $foto->hashName();

    // Verificar que la imagen se haya almacenado en el path correcto
    Storage::disk('public')->assertExists($friendlyPath);

    // Verificar la respuesta JSON contiene la URL de la imagen subida
    $response->assertJson([
        'url' => Storage::url($friendlyPath),
    ]);
});


it('allows a commerce to upload a foto and associates it', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Simular un archivo de imagen
    $foto = UploadedFile::fake()->image('commerce-foto.jpg');

    // Hacer la solicitud de subida de imagen para el comercio
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-image", [
        'foto' => $foto,
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Generar el path amigable
    $friendlyPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();

    // Verificar que la imagen se haya almacenado en el path correcto
    Storage::disk('public')->assertExists($friendlyPath);

    // Verificar que la imagen se haya asociado al comercio
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $commerce->id,
        'fotable_type' => \App\Models\Commerce::class,
        'path' => $friendlyPath,
    ]);

    // Verificar la respuesta JSON contiene la URL de la imagen subida
    $response->assertJson([
        'url' => Storage::url($friendlyPath),
    ]);
});


it('creates a foto and associates it with a commerce', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Simular un archivo de imagen
    $foto = UploadedFile::fake()->image('commerce-foto.jpg');

    // Hacer la solicitud de subida de imagen para el comercio
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-image", [
        'foto' => $foto,
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Generar el path amigable
    $friendlyPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();

    // Verificar que la imagen se haya almacenado en el path correcto
    Storage::disk('public')->assertExists($friendlyPath);

    // Verificar que la imagen se haya asociado correctamente al comercio
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $commerce->id,
        'fotable_type' => \App\Models\Commerce::class,
        'path' => $friendlyPath,
    ]);

    // Verificar que se ha creado una instancia de Foto en la base de datos
    $this->assertCount(1, Foto::where('fotable_id', $commerce->id)->get());
});

it('creates a foto and associates it with an nro', function () {
    // Asegurarse de que la base de datos esté limpia
    Foto::query()->delete();

    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO (institución)
    $nro = Nro::factory()->create();

    // Simular un archivo de imagen
    $foto = UploadedFile::fake()->image('nro-foto.jpg');

    // Hacer la solicitud de subida de imagen para la NRO
    $response = $this->postJson("/api/nros/{$nro->id}/upload-image", [
        'foto' => $foto,
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Generar el path amigable
    $friendlyPath = "fotos/nros/{$nro->id}/" . $foto->hashName();

    // Verificar que la imagen se haya almacenado en el path correcto
    Storage::disk('public')->assertExists($friendlyPath);

    // Verificar que la imagen se haya asociado correctamente a la NRO
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $nro->id,
        'fotable_type' => \App\Models\Nro::class,
        'path' => $friendlyPath,
    ]);

    // Verificar que solo se haya creado una instancia de Foto en la base de datos
    $this->assertCount(1, Foto::where('fotable_id', $nro->id)->get());
});


it('ensures friendly paths for uploaded fotos', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Simular un archivo de imagen
    $foto = UploadedFile::fake()->image('commerce-foto.jpg');

    // Hacer la solicitud de subida de imagen para el comercio
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-image", [
        'foto' => $foto,
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Generar el path amigable
    $friendlyPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();

    // Verificar que la imagen se haya almacenado en la ubicación correcta
    Storage::disk('public')->assertExists($friendlyPath);

    // Verificar que la imagen tiene el path amigable en la base de datos
    $this->assertDatabaseHas('fotos', [
        'fotable_id' => $commerce->id,
        'fotable_type' => \App\Models\Commerce::class,
        'path' => $friendlyPath,
    ]);
});

