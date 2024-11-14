<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Nro;

beforeEach(function () {
    Storage::fake('public'); // Usar disco falso para simular la subida de archivos
});

/**
 * Test para verificar que se puede subir y asignar un avatar a un comercio.
 */
it('allows a commerce to upload and assign an avatar', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un comercio
    $commerce = Commerce::factory()->create();

    // Simular un archivo de avatar
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    // Subir la imagen
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-avatar", [
        'avatar' => $avatar,
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Capturar el nombre del archivo como lo guarda Laravel
    $savedAvatarPath = "avatars/commerces/{$commerce->id}";

    // Verificar el contenido del directorio
    $uploadedFiles = Storage::disk('public')->files("avatars/commerces");

    // Verificar que el avatar fue almacenado en el path correcto
    Storage::disk('public')->assertExists($savedAvatarPath);

    if (!Storage::disk('public')->exists($savedAvatarPath)) {
        dump("El archivo no fue creado en el disco falso en la ruta: $savedAvatarPath");
    }

    // Verificar que el avatar está asociado correctamente al comercio
    $commerce->refresh();
    $this->assertEquals($savedAvatarPath, $commerce->avatar);
});


/**
 * Test para verificar que se utiliza un avatar por defecto si no se encuentra uno.
 */
it('uses a default avatar if no avatar is found for commerce', function () {
    $commerce = Commerce::factory()->create(['avatar' => null]);

    // Verificar que el avatar por defecto es asignado si no hay avatar
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png'); // Usar asset() para generar la URL completa
    $this->assertEquals($defaultAvatarUrl, $commerce->avatar_url);
});


/**
 * Test para verificar que se puede subir y asignar un avatar a una NRO.
 */
it('allows an nro to upload and assign an avatar', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular una NRO
    $nro = Nro::factory()->create();

    // Simular un archivo de imagen de avatar
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    // Subir el avatar
    $this->postJson("/api/nros/{$nro->id}/upload-avatar", [
        'avatar' => $avatar,
    ])->assertStatus(200);

    // Verificar que el avatar se ha almacenado correctamente en la ubicación especificada
    $avatarPath = "avatars/nros/{$nro->id}";
    Storage::disk('public')->assertExists($avatarPath);

    // Verificar que el avatar está asociado correctamente a la NRO
    $nro->refresh();
    $this->assertEquals($avatarPath, $nro->avatar);
});

/**
 * Test para verificar que se utiliza un avatar por defecto si no se encuentra uno para una NRO.
 */
it('uses a default avatar if no avatar is found for an nro', function () {
    $nro = Nro::factory()->create(['avatar' => null]);

    // Verificar que el avatar por defecto es asignado si no hay avatar
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png');
    $this->assertEquals($defaultAvatarUrl, $nro->avatar_url);
});

it('ensures commerce has an avatar or default avatar', function () {
    $commerce = Commerce::factory()->create(['avatar' => null]);

    // Verificar que el avatar tiene un valor por defecto
    $this->assertNotNull($commerce->avatar_url, 'El avatar debería tener un valor por defecto.');

    // Verificar que el avatar sea el avatar por defecto
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png');
    $this->assertEquals($defaultAvatarUrl, $commerce->avatar_url);
});


it('ensures commerce has a custom avatar if uploaded', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Crear un comercio
    $commerce = Commerce::factory()->create();

    // Simular la subida de una imagen para el avatar
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    // Hacer la solicitud de subida del avatar
    $this->postJson("/api/commerces/{$commerce->id}/upload-avatar", ['avatar' => $avatar])
        ->assertStatus(200);

    // Refrescar el modelo para obtener el avatar actualizado
    $commerce->refresh();

    // Verificar que el avatar no es null
    $this->assertNotNull($commerce->avatar_url);

    // Generar el path esperado del avatar con la extensión
    $expectedAvatarUrl = asset("storage/avatars/commerces/{$commerce->id}");

    // Verificar que el avatar es la imagen subida
    $this->assertEquals($expectedAvatarUrl, $commerce->avatar_url);
});

/**
 * Test para verificar que un usuario puede subir y asignar un nuevo avatar.
 */
it('allows a user to upload and assign a new avatar', function () {
    // Simular un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    // Simular un archivo de avatar
    $newAvatar = UploadedFile::fake()->image('new_avatar.jpg');

    // Subir la imagen
    $response = $this->postJson('/api/user/upload-avatar', [
        'avatar' => $newAvatar,
    ]);

    // Verificar que la respuesta es exitosa
    $response->assertStatus(200);

    // Capturar el nombre del archivo como lo guarda Laravel
    $savedAvatarPath = "avatars/users/{$user->id}";

    // Verificar que el avatar fue almacenado en el path correcto
    Storage::disk('public')->assertExists($savedAvatarPath);

    // Verificar que el avatar está asociado correctamente al usuario
    $user->refresh();
    $this->assertEquals($savedAvatarPath, $user->profile_photo_path);
});

/**
 * Test para verificar que se utiliza un avatar por defecto si no se encuentra uno para el usuario.
 */
it('uses a default avatar if no avatar is found for user', function () {
    $user = User::factory()->create(['profile_photo_path' => null]);

    $nameParts = explode(' ', $user->name);
    $initials = implode('+', array_map(fn($part) => strtoupper(substr($part, 0, 1)), $nameParts));

    // Verificar que el URL del avatar se genera correctamente con las iniciales
    $expectedAvatarUrl = 'https://ui-avatars.com/api/?name=' . $initials . '&color=7F9CF5&background=EBF4FF';
    // Verificar que el avatar por defecto es asignado si no hay avatar
    $this->assertEquals($expectedAvatarUrl, $user->profile_photo_url);
});

