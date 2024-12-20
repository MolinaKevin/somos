<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Foto;

beforeEach(function () {
    Storage::fake('public'); 
});

/**
 * Test para verificar que un comercio tiene varias fotos asociadas.
 */
it('ensures a commerce has multiple fotos associated', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        UploadedFile::fake()->image('foto3.jpg')
    ];

    
    foreach ($fotos as $foto) {
        $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    
    $this->assertCount(3, $commerce->fotos);
});

/**
 * Test para verificar que se puede seleccionar una foto como background_image.
 */
it('allows a commerce to select a background image from its associated fotos', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $foto = UploadedFile::fake()->image('background-image.jpg');

    
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
        ->assertStatus(200);

    
    $fotoRecord = $commerce->fotos()->first();

    
    $commerce->background_image_id = $fotoRecord->id;
    $commerce->save();

    
    $this->assertEquals(asset(Storage::url($fotoRecord->path)), $commerce->background_image);
});


/**
 * Test para verificar que se actualiza correctamente el background_image.
 */
it('updates the background image when a new image is selected', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $foto1 = UploadedFile::fake()->image('commerce-foto1.jpg');
    $foto2 = UploadedFile::fake()->image('commerce-foto2.jpg');

    
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto1])
        ->assertStatus(200);
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto2])
        ->assertStatus(200);

    
    $foto1Path = "fotos/commerces/{$commerce->id}/" . $foto1->hashName();
    $foto1Record = $commerce->fotos()->where('path', $foto1Path)->first();
    $this->assertNotNull($foto1Record, 'La primera foto no fue encontrada.');

    
    $commerce->background_image_id = $foto1Record->id;
    $commerce->save();

    
    $this->assertEquals(asset(Storage::url($foto1Record->path)), $commerce->background_image);

    
    $foto2Path = "fotos/commerces/{$commerce->id}/" . $foto2->hashName();
    $foto2Record = $commerce->fotos()->where('path', $foto2Path)->first();
    $this->assertNotNull($foto2Record, 'La segunda foto no fue encontrada.');

    
    $commerce->background_image_id = $foto2Record->id;
    $commerce->save();

    
    $this->assertEquals(asset(Storage::url($foto2Record->path)), $commerce->background_image);
});

/**
 * Test para verificar que se actualiza el background_image_id correctamente cuando se carga una nueva imagen.
 */
it('updates background_image_id when a new image is uploaded', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $foto = UploadedFile::fake()->image('new-background-image.jpg');

    
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
        ->assertStatus(200);

    
    $fotoPath = "fotos/commerces/{$commerce->id}/" . $foto->hashName();
    $fotoRecord = Foto::where('path', $fotoPath)->first();

    $this->assertNotNull($fotoRecord, 'La foto no fue encontrada.');

    
    $commerce->background_image_id = $fotoRecord->id;
    $commerce->save();

    
    $this->assertEquals($fotoRecord->id, $commerce->background_image_id);
});

/**
 * Test para verificar que se borra el background_image_id si se selecciona la imagen por defecto.
 */
/**
 * Test para verificar que se borra el background_image_id si se selecciona la imagen por defecto.
 */
it('removes background_image_id when the default image is selected', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $foto = Foto::factory()->create([
        'fotable_id' => $commerce->id,  
        'fotable_type' => Commerce::class,
    ]);

    
    $commerce->background_image_id = $foto->id;
    $commerce->save();

    
    $commerce->background_image_id = null;
    $commerce->save();

    
    $this->assertNull($commerce->background_image_id);
});

