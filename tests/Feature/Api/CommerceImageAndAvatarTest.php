<?php

use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Foto;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public'); // Usar disco falso para simular la subida de archivos
});

it('returns commerce with avatar and background image URLs', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio y asociarlo al usuario
    $commerce = Commerce::factory()->create();
    $user->commerces()->attach($commerce->id); // Asociar el comercio al usuario

    // Simular un archivo de avatar
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    // Subir el avatar
    $this->postJson("/api/commerces/{$commerce->id}/upload-avatar", [
        'avatar' => $avatar,
    ])->assertStatus(200);

    // Verificar si el avatar fue creado correctamente
    $avatarPath = "avatars/commerces/{$commerce->id}.jpg";

    // Obtener la lista de comercios
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);

    // Verificar los datos
    $responseData = $response->json('data');

    // Asegurarse de que la respuesta no esté vacía
    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    // Verificar que las URLs sean las correctas
    $expectedAvatarUrl = asset("storage/avatars/commerces/{$commerce->id}");

    // Si no hay background_image_id, esperar la imagen por defecto
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
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio sin avatar
    $commerce = Commerce::factory()->create(['avatar' => null]);

    // Asociar el comercio al usuario
    $user->commerces()->attach($commerce->id);

    // Llamada al endpoint para obtener la lista de comercios del usuario
    $response = $this->getJson('/api/user/commerces');

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Depurar el contenido completo de la respuesta
    $responseData = $response->json();

    // Asegurarse de que la respuesta contiene datos
    $this->assertNotEmpty($responseData['data'], 'La respuesta no contiene datos.');

    // Verificar que se utiliza el avatar por defecto
    $commerceData = $responseData['data'][0]; // Asegurarse de que hay datos antes de acceder al índice 0
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png');

    $this->assertEquals($defaultAvatarUrl, $commerceData['avatar_url']);
});

it('returns commerce with associated fotos URLs', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio y asociarlo al usuario
    $commerce = Commerce::factory()->create();
    $user->commerces()->attach($commerce->id); // Asociar el comercio al usuario

    // Simular archivos de imagen (fotos)
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        UploadedFile::fake()->image('foto3.jpg')
    ];

    // Subir cada imagen simulada y asociarla al comercio
    foreach ($fotos as $foto) {
        $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    // Obtener la lista de comercios del usuario
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);

    // Verificar los datos
    $responseData = $response->json('data');

    // Asegurarse de que la respuesta no está vacía
    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    // Verificar que las imágenes subidas están asociadas al comercio en la respuesta
    $commerceData = $responseData[0];
    $this->assertArrayHasKey('fotos_urls', $commerceData, 'Las fotos no fueron devueltas con el comercio.');
    
    // Verificar que el comercio tiene 3 fotos y que las URLs son correctas
    $this->assertCount(3, $commerceData['fotos_urls'], 'El comercio no tiene 3 fotos asociadas.');

    // Recuperar las fotos del comercio desde la base de datos
    $fotosEnBD = $commerce->fotos;

    // Verificar que las URLs de las fotos en la respuesta coinciden con las de la base de datos
    foreach ($fotosEnBD as $index => $fotoEnBD) {
        $expectedFotoUrl = asset('storage/' . $fotoEnBD->path);
        $this->assertEquals($expectedFotoUrl, $commerceData['fotos_urls'][$index], "La URL de la foto {$index} no coincide.");
    }
});


/**
 * Test para verificar que la imagen seleccionada como background_image no aparece en fotos_urls.
 */

it('excludes the background_image from fotos_urls', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();
    $user->commerces()->attach($commerce->id); // Asociar el comercio al usuario

    // Simular archivos de imagen
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        $backgroundImage = UploadedFile::fake()->image('background-image.jpg')
    ];

    // Subir cada imagen
    foreach ($fotos as $foto) {
        $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    // Usar el hashName() para obtener el nombre real de la imagen subida
    $backgroundImagePath = "fotos/commerces/{$commerce->id}/" . $backgroundImage->hashName();

    // Asignar la imagen seleccionada como el background_image_id
    $backgroundImageRecord = $commerce->fotos()->where('path', $backgroundImagePath)->first();
    $this->assertNotNull($backgroundImageRecord, 'La imagen de fondo no fue encontrada.');

    $commerce->background_image_id = $backgroundImageRecord->id;
    $commerce->save();

    // Obtener la lista de comercios y fotos
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);

    // Verificar los datos de la respuesta
    $responseData = $response->json('data');

    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    // Obtener los datos del comercio y verificar que las fotos están en fotos_urls
    $commerceData = $responseData[0];
    $this->assertArrayHasKey('fotos_urls', $commerceData, 'El atributo fotos_urls no existe en la respuesta.');

    // Verificar que el background_image no esté en fotos_urls
    foreach ($commerceData['fotos_urls'] as $fotoUrl) {
        $this->assertNotEquals(asset(Storage::url($backgroundImageRecord->path)), $fotoUrl, 'El background_image no debería estar en fotos_urls.');
    }

    // Verificar que solo hay dos fotos en fotos_urls, excluyendo el background_image
    $this->assertCount(2, $commerceData['fotos_urls'], 'La cantidad de fotos en fotos_urls no es correcta.');
});
