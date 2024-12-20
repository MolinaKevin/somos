<?php

use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\Nro;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public'); 
});

it('returns nro with avatar and background image URLs', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id); 

    
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    
    $this->postJson("/api/nros/{$nro->id}/upload-avatar", [
        'avatar' => $avatar,
    ])->assertStatus(200);

    
    $avatarPath = "avatars/nros/{$nro->id}.jpg";

    
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);

    
    $responseData = $response->json('data');

    
    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    
    $expectedAvatarUrl = asset("storage/avatars/nros/{$nro->id}");

    
    if ($nro->background_image_id === null) {
        $expectedBackgroundUrl = asset("storage/fotos/nros/default_background.jpg");
    } else {
        $expectedBackgroundUrl = asset("storage/fotos/nros/{$nro->id}/background.jpg");
    }

    $nroData = $responseData[0];

    $this->assertEquals($expectedAvatarUrl, $nroData['avatar_url']);
    $this->assertEquals($expectedBackgroundUrl, $nroData['background_image']);
});

it('returns default avatar if no avatar is assigned to nro', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create(['avatar' => null]);

    
    $nro->users()->attach($user->id);


    
    $response = $this->getJson('/api/user/nros');

    
    $response->assertStatus(200);

    
    $responseData = $response->json();

    
    $this->assertNotEmpty($responseData['data'], 'La respuesta no contiene datos.');

    
    $nroData = $responseData['data'][0]; 
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png');

    $this->assertEquals($defaultAvatarUrl, $nroData['avatar_url']);
});

it('returns nro with associated fotos URLs', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();
    $user->nros()->attach($nro->id); 

    
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        UploadedFile::fake()->image('foto3.jpg')
    ];

    
    foreach ($fotos as $foto) {
        $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);

    
    $responseData = $response->json('data');

    
    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    
    $nroData = $responseData[0];
    $this->assertArrayHasKey('fotos_urls', $nroData, 'Las fotos no fueron devueltas con la NRO.');
    
    
    $this->assertCount(3, $nroData['fotos_urls'], 'La NRO no tiene 3 fotos asociadas.');

    
    $fotosEnBD = $nro->fotos;

    
    foreach ($fotosEnBD as $index => $fotoEnBD) {
        $expectedFotoUrl = asset('storage/' . $fotoEnBD->path);
        $this->assertEquals($expectedFotoUrl, $nroData['fotos_urls'][$index], "La URL de la foto {$index} no coincide.");
    }
});

/**
 * Test para verificar que la imagen seleccionada como background_image no aparece en fotos_urls en una NRO.
 */
it('excludes the background_image from fotos_urls for nro', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();
    $user->nros()->attach($nro->id); 

    
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        $backgroundImage = UploadedFile::fake()->image('background-image.jpg')
    ];

    
    foreach ($fotos as $foto) {
        $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    
    $backgroundImagePath = "fotos/nros/{$nro->id}/" . $backgroundImage->hashName();

    
    $backgroundImageRecord = $nro->fotos()->where('path', $backgroundImagePath)->first();
    $this->assertNotNull($backgroundImageRecord, 'La imagen de fondo no fue encontrada.');

    $nro->background_image_id = $backgroundImageRecord->id;
    $nro->save();

    
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);

    
    $responseData = $response->json('data');

    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    
    $nroData = $responseData[0];
    $this->assertArrayHasKey('fotos_urls', $nroData, 'El atributo fotos_urls no existe en la respuesta.');

    
    foreach ($nroData['fotos_urls'] as $fotoUrl) {
        $this->assertNotEquals(asset(Storage::url($backgroundImageRecord->path)), $fotoUrl, 'El background_image no debería estar en fotos_urls.');
    }

    
    $this->assertCount(2, $nroData['fotos_urls'], 'La cantidad de fotos en fotos_urls no es correcta.');
});

