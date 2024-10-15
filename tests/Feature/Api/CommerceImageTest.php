<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Foto;

beforeEach(function () {
    Storage::fake('public'); // Usar un disco falso para simular la subida de archivos
});

/**
 * Test para verificar que un comercio tiene varias fotos asociadas.
 */
it('ensures a commerce has multiple fotos associated', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Simular archivos de imagen
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        UploadedFile::fake()->image('foto3.jpg')
    ];

    // Subir cada imagen
    foreach ($fotos as $foto) {
        $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    // Verificar que el comercio tiene 3 fotos asociadas
    $this->assertCount(3, $commerce->fotos);
});

/**
 * Test para verificar que se puede seleccionar una foto como background_image.
 */
it('allows a commerce to select a background image from its associated fotos', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Simular archivo de imagen
    $foto = UploadedFile::fake()->image('background-image.jpg');

    // Subir la imagen
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
        ->assertStatus(200);

    // Simular la selección de una foto como background_image
    $fotoRecord = $commerce->fotos()->first();

    // Asignar la imagen seleccionada como el background_image_id
    $commerce->background_image_id = $fotoRecord->id;
    $commerce->save();

    // Verificar que el background_image se ha actualizado correctamente
    $this->assertEquals(asset(Storage::url($fotoRecord->path)), $commerce->background_image);
});


/**
 * Test para verificar que se actualiza correctamente el background_image.
 */
it('updates the background image when a new image is selected', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Subir las fotos simuladas
    $foto1 = UploadedFile::fake()->image('commerce-foto1.jpg');
    $foto2 = UploadedFile::fake()->image('commerce-foto2.jpg');

    // Subir las imágenes y obtener el path del almacenamiento
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto1])
        ->assertStatus(200);
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto2])
        ->assertStatus(200);

    // Verificar que la primera foto se ha almacenado en la base de datos
    $foto1Path = "fotos/commerces/{$commerce->id}/" . $foto1->hashName();
    $foto1Record = $commerce->fotos()->where('path', $foto1Path)->first();
    $this->assertNotNull($foto1Record, 'La primera foto no fue encontrada.');

    // Asignar la primera imagen como background_image_id
    $commerce->background_image_id = $foto1Record->id;
    $commerce->save();

    // Verificar que el background_image es correcto
    $this->assertEquals(asset(Storage::url($foto1Record->path)), $commerce->background_image);

    // Verificar que la segunda foto se ha almacenado en la base de datos
    $foto2Path = "fotos/commerces/{$commerce->id}/" . $foto2->hashName();
    $foto2Record = $commerce->fotos()->where('path', $foto2Path)->first();
    $this->assertNotNull($foto2Record, 'La segunda foto no fue encontrada.');

    // Asignar la segunda imagen como nuevo background_image_id
    $commerce->background_image_id = $foto2Record->id;
    $commerce->save();

    // Verificar que el background_image se ha actualizado correctamente
    $this->assertEquals(asset(Storage::url($foto2Record->path)), $commerce->background_image);
});

/**
 * Test para verificar que se actualiza el background_image_id correctamente cuando se carga una nueva imagen.
 */
it('updates background_image_id when a new image is uploaded', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Subir una imagen simulada
    $foto = UploadedFile::fake()->image('new-background-image.jpg');

    // Subir la imagen
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
        ->assertStatus(200);

    // Verificar que la foto se ha almacenado correctamente en la base de datos
    $fotoPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();
    $fotoRecord = Foto::where('path', $fotoPath)->first();

    $this->assertNotNull($fotoRecord, 'La foto no fue encontrada.');

    // Actualizar el background_image_id del comercio con la nueva imagen
    $commerce->background_image_id = $fotoRecord->id;
    $commerce->save();

    // Verificar que el background_image_id se ha actualizado correctamente
    $this->assertEquals($fotoRecord->id, $commerce->background_image_id);
});

/**
 * Test para verificar que se borra el background_image_id si se selecciona la imagen por defecto.
 */
/**
 * Test para verificar que se borra el background_image_id si se selecciona la imagen por defecto.
 */
it('removes background_image_id when the default image is selected', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Crear el comercio primero
    $commerce = Commerce::factory()->create();

    // Luego, crear la foto asociada al comercio
    $foto = Foto::factory()->create([
        'fotable_id' => $commerce->id,  // Asociamos la foto al comercio recién creado
        'fotable_type' => Commerce::class,
    ]);

    // Asignar la foto como background_image_id
    $commerce->background_image_id = $foto->id;
    $commerce->save();

    // Simular la selección de la imagen por defecto
    $commerce->background_image_id = null;
    $commerce->save();

    // Verificar que el background_image_id se ha eliminado correctamente
    $this->assertNull($commerce->background_image_id);
});

