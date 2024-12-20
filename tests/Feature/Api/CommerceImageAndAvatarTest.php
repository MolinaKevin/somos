<?php

use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Foto;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public'); 
});

it('returns commerce with avatar and background image URLs', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();
    $user->commerces()->attach($commerce->id); 

    
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    
    $this->postJson("/api/commerces/{$commerce->id}/upload-avatar", [
        'avatar' => $avatar,
    ])->assertStatus(200);

    
    $avatarPath = "avatars/commerces/{$commerce->id}.jpg";

    
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);

    
    $responseData = $response->json('data');

    
    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    
    $expectedAvatarUrl = asset("storage/avatars/commerces/{$commerce->id}");

    
    if ($commerce->background_image_id === null) {
        $expectedBackgroundUrl = asset("storage/fotos/commerces/default_background.jpg");
    } else {
        $expectedBackgroundUrl = asset("storage/fotos/commerces/{$commerce->id}/background.jpg");
    }

    $commerceData = $responseData[0];

    $this->assertEquals($expectedAvatarUrl, $commerceData['avatar_url']);
    $this->assertEquals($expectedBackgroundUrl, $commerceData['background_image']);
});

it('returns default avatar if no avatar is assigned to commerce', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create(['avatar' => null]);

    
    $user->commerces()->attach($commerce->id);

    
    $response = $this->getJson('/api/user/commerces');

    
    $response->assertStatus(200);

    
    $responseData = $response->json();

    
    $this->assertNotEmpty($responseData['data'], 'La respuesta no contiene datos.');

    
    $commerceData = $responseData['data'][0]; 
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png');

    $this->assertEquals($defaultAvatarUrl, $commerceData['avatar_url']);
});

it('returns commerce with associated fotos URLs', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();
    $user->commerces()->attach($commerce->id); 

    
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        UploadedFile::fake()->image('foto3.jpg')
    ];

    
    foreach ($fotos as $foto) {
        $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);

    
    $responseData = $response->json('data');

    
    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    
    $commerceData = $responseData[0];
    $this->assertArrayHasKey('fotos_urls', $commerceData, 'Las fotos no fueron devueltas con el comercio.');
    
    
    $this->assertCount(3, $commerceData['fotos_urls'], 'El comercio no tiene 3 fotos asociadas.');

    
    $fotosEnBD = $commerce->fotos;

    
    foreach ($fotosEnBD as $index => $fotoEnBD) {
        $expectedFotoUrl = asset('storage/' . $fotoEnBD->path);
        $this->assertEquals($expectedFotoUrl, $commerceData['fotos_urls'][$index], "La URL de la foto {$index} no coincide.");
    }
});


/**
 * Test para verificar que la imagen seleccionada como background_image no aparece en fotos_urls.
 */

it('excludes the background_image from fotos_urls', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();
    $user->commerces()->attach($commerce->id); 

    
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        $backgroundImage = UploadedFile::fake()->image('background-image.jpg')
    ];

    
    foreach ($fotos as $foto) {
        $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    
    $backgroundImagePath = "fotos/commerces/{$commerce->id}/" . $backgroundImage->hashName();

    
    $backgroundImageRecord = $commerce->fotos()->where('path', $backgroundImagePath)->first();
    $this->assertNotNull($backgroundImageRecord, 'La imagen de fondo no fue encontrada.');

    $commerce->background_image_id = $backgroundImageRecord->id;
    $commerce->save();

    
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);

    
    $responseData = $response->json('data');

    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    
    $commerceData = $responseData[0];
    $this->assertArrayHasKey('fotos_urls', $commerceData, 'El atributo fotos_urls no existe en la respuesta.');

    
    foreach ($commerceData['fotos_urls'] as $fotoUrl) {
        $this->assertNotEquals(asset(Storage::url($backgroundImageRecord->path)), $fotoUrl, 'El background_image no debería estar en fotos_urls.');
    }

    
    $this->assertCount(2, $commerceData['fotos_urls'], 'La cantidad de fotos en fotos_urls no es correcta.');
});
