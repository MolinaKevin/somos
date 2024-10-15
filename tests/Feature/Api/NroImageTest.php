<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Nro;
use App\Models\Foto;

beforeEach(function () {
    Storage::fake('public'); // Usar un disco falso para simular la subida de archivos
});

/**
 * Test para verificar que una NRO tiene varias fotos asociadas.
 */
it('ensures an nro has multiple fotos associated', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO
    $nro = Nro::factory()->create();

    // Simular archivos de imagen
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        UploadedFile::fake()->image('foto3.jpg')
    ];

    // Subir cada imagen
    foreach ($fotos as $foto) {
        $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    // Verificar que la NRO tiene 3 fotos asociadas
    $this->assertCount(3, $nro->fotos);
});

/**
 * Test para verificar que se puede seleccionar una foto como background_image en una NRO.
 */
it('allows an nro to select a background image from its associated fotos', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO
    $nro = Nro::factory()->create();

    // Simular archivo de imagen
    $foto = UploadedFile::fake()->image('background-image.jpg');

    // Subir la imagen
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
        ->assertStatus(200);

    // Simular la selección de una foto como background_image
    $fotoRecord = $nro->fotos()->first();

    // Asignar la imagen seleccionada como el background_image_id
    $nro->background_image_id = $fotoRecord->id;
    $nro->save();

    // Verificar que el background_image se ha actualizado correctamente
    $this->assertEquals(asset(Storage::url($fotoRecord->path)), $nro->background_image);
});

it('verifies that background_image_id exists in the commerce table', function () {
    $columns = Schema::getColumnListing('commerces');
    $this->assertTrue(in_array('background_image_id', $columns), 'background_image_id no existe en la tabla commerces.');
});


/**
 * Test para verificar que se actualiza correctamente el background_image de una NRO.
 */
it('updates the background image for an nro when a new image is selected', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO
    $nro = Nro::factory()->create();

    // Subir las fotos simuladas
    $foto1 = UploadedFile::fake()->image('nro-foto1.jpg');
    $foto2 = UploadedFile::fake()->image('nro-foto2.jpg');

    // Subir las imágenes y obtener el path del almacenamiento
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto1])
        ->assertStatus(200);
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto2])
        ->assertStatus(200);

    // Verificar que la primera foto se ha almacenado en la base de datos
    $foto1Path = "fotos/nros/{$nro->id}/" . $foto1->hashName();
    $foto1Record = $nro->fotos()->where('path', $foto1Path)->first();
    $this->assertNotNull($foto1Record, 'La primera foto no fue encontrada.');

    // Asignar la primera imagen como background_image_id
    $nro->background_image_id = $foto1Record->id;
    $nro->save();

    // Verificar que el background_image es correcto
    $this->assertEquals(asset(Storage::url($foto1Record->path)), $nro->background_image);

    // Verificar que la segunda foto se ha almacenado en la base de datos
    $foto2Path = "fotos/nros/{$nro->id}/" . $foto2->hashName();
    $foto2Record = $nro->fotos()->where('path', $foto2Path)->first();
    $this->assertNotNull($foto2Record, 'La segunda foto no fue encontrada.');

    // Asignar la segunda imagen como nuevo background_image_id
    $nro->background_image_id = $foto2Record->id;
    $nro->save();

    // Verificar que el background_image se ha actualizado correctamente
    $this->assertEquals($nro->background_image_id, $foto2Record->id);
    $this->assertEquals(asset(Storage::url($foto2Record->path)), $nro->background_image);
});

/**
 * Test para verificar que un Nro puede seleccionar una imagen de fondo de entre sus fotos asociadas.
 */
it('allows an nro to select a background image from its associated fotos 2', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un NRO
    $nro = Nro::factory()->create();

    // Simular archivo de imagen
    $foto = UploadedFile::fake()->image('background-image.jpg');

    // Subir la imagen
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
        ->assertStatus(200);

    // Obtener el primer registro de la foto
    $fotoRecord = $nro->fotos()->first();

    // Asignar la imagen seleccionada como el background_image_id
    $nro->background_image_id = $fotoRecord->id;
    $nro->save();

    // Verificar que el background_image se ha actualizado correctamente
    $this->assertEquals(asset(Storage::url($fotoRecord->path)), $nro->background_image);
});

/**
 * Test para verificar que se borra el background_image_id si se selecciona la imagen por defecto en Nro.
 */
it('removes background_image_id when the default image is selected for an nro', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Crear el NRO primero
    $nro = Nro::factory()->create();

    // Luego, crear la foto asociada al NRO
    $foto = Foto::factory()->create([
        'fotable_id' => $nro->id,  // Asociamos la foto al NRO recién creado
        'fotable_type' => Nro::class,
    ]);

    // Asignar la foto como background_image_id
    $nro->background_image_id = $foto->id;
    $nro->save();

    // Simular la selección de la imagen por defecto
    $nro->background_image_id = null;
    $nro->save();

    // Verificar que el background_image_id se ha eliminado correctamente
    $this->assertNull($nro->background_image_id);
});

