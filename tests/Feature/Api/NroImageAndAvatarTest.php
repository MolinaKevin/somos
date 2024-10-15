<?php

use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\Nro;
use App\Models\User;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    Storage::fake('public'); // Usar disco falso para simular la subida de archivos
});

it('returns nro with avatar and background image URLs', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO y asociarla al usuario
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id); // Asociar la NRO al usuario

    // Simular un archivo de avatar
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    // Subir el avatar
    $this->postJson("/api/nros/{$nro->id}/upload-avatar", [
        'avatar' => $avatar,
    ])->assertStatus(200);

    // Verificar si el avatar fue creado correctamente
    $avatarPath = "avatars/nros/{$nro->id}.jpg";

    // Obtener la lista de NROs
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);

    // Verificar los datos
    $responseData = $response->json('data');

    // Asegurarse de que la respuesta no esté vacía
    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    // Verificar que las URLs sean las correctas
    $expectedAvatarUrl = asset("storage/avatars/nros/{$nro->id}");

    // Si no hay background_image_id, esperar la imagen por defecto
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
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO sin avatar
    $nro = Nro::factory()->create(['avatar' => null]);

    // Asociar la NRO al usuario
    $nro->users()->attach($user->id);


    // Llamada al endpoint para obtener la lista de NROs del usuario
    $response = $this->getJson('/api/user/nros');

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Depurar el contenido completo de la respuesta
    $responseData = $response->json();

    // Asegurarse de que la respuesta contiene datos
    $this->assertNotEmpty($responseData['data'], 'La respuesta no contiene datos.');

    // Verificar que se utiliza el avatar por defecto
    $nroData = $responseData['data'][0]; // Asegurarse de que hay datos antes de acceder al índice 0
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png');

    $this->assertEquals($defaultAvatarUrl, $nroData['avatar_url']);
});

it('returns nro with associated fotos URLs', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO y asociarla al usuario
    $nro = Nro::factory()->create();
    $user->nros()->attach($nro->id); // Asociar la NRO al usuario

    // Simular archivos de imagen (fotos)
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        UploadedFile::fake()->image('foto3.jpg')
    ];

    // Subir cada imagen simulada y asociarla a la NRO
    foreach ($fotos as $foto) {
        $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    // Obtener la lista de NROs del usuario
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);

    // Verificar los datos
    $responseData = $response->json('data');

    // Asegurarse de que la respuesta no está vacía
    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    // Verificar que las imágenes subidas están asociadas a la NRO en la respuesta
    $nroData = $responseData[0];
    $this->assertArrayHasKey('fotos_urls', $nroData, 'Las fotos no fueron devueltas con la NRO.');
    
    // Verificar que la NRO tiene 3 fotos y que las URLs son correctas
    $this->assertCount(3, $nroData['fotos_urls'], 'La NRO no tiene 3 fotos asociadas.');

    // Recuperar las fotos de la NRO desde la base de datos
    $fotosEnBD = $nro->fotos;

    // Verificar que las URLs de las fotos en la respuesta coinciden con las de la base de datos
    foreach ($fotosEnBD as $index => $fotoEnBD) {
        $expectedFotoUrl = asset('storage/' . $fotoEnBD->path);
        $this->assertEquals($expectedFotoUrl, $nroData['fotos_urls'][$index], "La URL de la foto {$index} no coincide.");
    }
});

/**
 * Test para verificar que la imagen seleccionada como background_image no aparece en fotos_urls en una NRO.
 */
it('excludes the background_image from fotos_urls for nro', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO
    $nro = Nro::factory()->create();
    $user->nros()->attach($nro->id); // Asociar la NRO al usuario

    // Simular archivos de imagen
    $fotos = [
        UploadedFile::fake()->image('foto1.jpg'),
        UploadedFile::fake()->image('foto2.jpg'),
        $backgroundImage = UploadedFile::fake()->image('background-image.jpg')
    ];

    // Subir cada imagen
    foreach ($fotos as $foto) {
        $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $foto])
            ->assertStatus(200);
    }

    // Usar el hashName() para obtener el nombre real de la imagen subida
    $backgroundImagePath = "fotos/nros/{$nro->id}/" . $backgroundImage->hashName();

    // Asignar la imagen seleccionada como el background_image_id
    $backgroundImageRecord = $nro->fotos()->where('path', $backgroundImagePath)->first();
    $this->assertNotNull($backgroundImageRecord, 'La imagen de fondo no fue encontrada.');

    $nro->background_image_id = $backgroundImageRecord->id;
    $nro->save();

    // Obtener la lista de NROs y fotos
    $response = $this->getJson('/api/user/nros');
    $response->assertStatus(200);

    // Verificar los datos de la respuesta
    $responseData = $response->json('data');

    $this->assertNotEmpty($responseData, 'La respuesta no contiene datos.');

    // Obtener los datos de la NRO y verificar que las fotos están en fotos_urls
    $nroData = $responseData[0];
    $this->assertArrayHasKey('fotos_urls', $nroData, 'El atributo fotos_urls no existe en la respuesta.');

    // Verificar que el background_image no esté en fotos_urls
    foreach ($nroData['fotos_urls'] as $fotoUrl) {
        $this->assertNotEquals(asset(Storage::url($backgroundImageRecord->path)), $fotoUrl, 'El background_image no debería estar en fotos_urls.');
    }

    // Verificar que solo hay dos fotos en fotos_urls, excluyendo el background_image
    $this->assertCount(2, $nroData['fotos_urls'], 'La cantidad de fotos en fotos_urls no es correcta.');
});

