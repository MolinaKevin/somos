<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Nro;
use App\Models\Foto;

beforeEach(function () {
    Storage::fake('public'); 
});

/**
 * Test para verificar que una NRO tiene varias fotos asociadas.
 */
it('ensures an nro has multiple fotos associated', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();

    
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        UploadedFile::fake()->image('foto3.jpg')
    ];

    
    foreach ($fotos as $foto) {
        $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    
    $this->assertCount(3, $nro->fotos);
});

/**
 * Test para verificar que se puede seleccionar una foto como background_image en una NRO.
 */
it('allows an nro to select a background image from its associated fotos', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();

    
    $foto = UploadedFile::fake()->image('background-image.jpg');

    
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
        ->assertStatus(200);

    
    $fotoRecord = $nro->fotos()->first();

    
    $nro->background_image_id = $fotoRecord->id;
    $nro->save();

    
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
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();

    
    $foto1 = UploadedFile::fake()->image('nro-foto1.jpg');
    $foto2 = UploadedFile::fake()->image('nro-foto2.jpg');

    
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto1])
        ->assertStatus(200);
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto2])
        ->assertStatus(200);

    
    $foto1Path = "fotos/nros/{$nro->id}/" . $foto1->hashName();
    $foto1Record = $nro->fotos()->where('path', $foto1Path)->first();
    $this->assertNotNull($foto1Record, 'La primera foto no fue encontrada.');

    
    $nro->background_image_id = $foto1Record->id;
    $nro->save();

    
    $this->assertEquals(asset(Storage::url($foto1Record->path)), $nro->background_image);

    
    $foto2Path = "fotos/nros/{$nro->id}/" . $foto2->hashName();
    $foto2Record = $nro->fotos()->where('path', $foto2Path)->first();
    $this->assertNotNull($foto2Record, 'La segunda foto no fue encontrada.');

    
    $nro->background_image_id = $foto2Record->id;
    $nro->save();

    
    $this->assertEquals($nro->background_image_id, $foto2Record->id);
    $this->assertEquals(asset(Storage::url($foto2Record->path)), $nro->background_image);
});

/**
 * Test para verificar que un Nro puede seleccionar una imagen de fondo de entre sus fotos asociadas.
 */
it('allows an nro to select a background image from its associated fotos 2', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();

    
    $foto = UploadedFile::fake()->image('background-image.jpg');

    
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
        ->assertStatus(200);

    
    $fotoRecord = $nro->fotos()->first();

    
    $nro->background_image_id = $fotoRecord->id;
    $nro->save();

    
    $this->assertEquals(asset(Storage::url($fotoRecord->path)), $nro->background_image);
});

/**
 * Test para verificar que se borra el background_image_id si se selecciona la imagen por defecto en Nro.
 */
it('removes background_image_id when the default image is selected for an nro', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();

    
    $foto = Foto::factory()->create([
        'fotable_id' => $nro->id,  
        'fotable_type' => Nro::class,
    ]);

    
    $nro->background_image_id = $foto->id;
    $nro->save();

    
    $nro->background_image_id = null;
    $nro->save();

    
    $this->assertNull($nro->background_image_id);
});

