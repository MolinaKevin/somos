<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Nro;

beforeEach(function () {
    Storage::fake('public'); 
});

/**
 * Test para verificar que se puede subir y asignar un avatar a un comercio.
 */
it('allows a commerce to upload and assign an avatar', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    
    $response = $this->postJson("/api/commerces/{$commerce->id}/upload-avatar", [
        'avatar' => $avatar,
    ]);

    
    $response->assertStatus(200);

    
    $savedAvatarPath = "avatars/commerces/{$commerce->id}";

    
    $uploadedFiles = Storage::disk('public')->files("avatars/commerces");

    
    Storage::disk('public')->assertExists($savedAvatarPath);

    if (!Storage::disk('public')->exists($savedAvatarPath)) {
        dump("El archivo no fue creado en el disco falso en la ruta: $savedAvatarPath");
    }

    
    $commerce->refresh();
    $this->assertEquals($savedAvatarPath, $commerce->avatar);
});


/**
 * Test para verificar que se utiliza un avatar por defecto si no se encuentra uno.
 */
it('uses a default avatar if no avatar is found for commerce', function () {
    $commerce = Commerce::factory()->create(['avatar' => null]);

    
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png'); 
    $this->assertEquals($defaultAvatarUrl, $commerce->avatar_url);
});


/**
 * Test para verificar que se puede subir y asignar un avatar a una NRO.
 */
it('allows an nro to upload and assign an avatar', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $nro = Nro::factory()->create();

    
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    
    $this->postJson("/api/nros/{$nro->id}/upload-avatar", [
        'avatar' => $avatar,
    ])->assertStatus(200);

    
    $avatarPath = "avatars/nros/{$nro->id}";
    Storage::disk('public')->assertExists($avatarPath);

    
    $nro->refresh();
    $this->assertEquals($avatarPath, $nro->avatar);
});

/**
 * Test para verificar que se utiliza un avatar por defecto si no se encuentra uno para una NRO.
 */
it('uses a default avatar if no avatar is found for an nro', function () {
    $nro = Nro::factory()->create(['avatar' => null]);

    
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png');
    $this->assertEquals($defaultAvatarUrl, $nro->avatar_url);
});

it('ensures commerce has an avatar or default avatar', function () {
    $commerce = Commerce::factory()->create(['avatar' => null]);

    
    $this->assertNotNull($commerce->avatar_url, 'El avatar debería tener un valor por defecto.');

    
    $defaultAvatarUrl = asset('storage/avatars/avatar_fake.png');
    $this->assertEquals($defaultAvatarUrl, $commerce->avatar_url);
});


it('ensures commerce has a custom avatar if uploaded', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $commerce = Commerce::factory()->create();

    
    $avatar = UploadedFile::fake()->image('avatar.jpg');

    
    $this->postJson("/api/commerces/{$commerce->id}/upload-avatar", ['avatar' => $avatar])
        ->assertStatus(200);

    
    $commerce->refresh();

    
    $this->assertNotNull($commerce->avatar_url);

    
    $expectedAvatarUrl = asset("storage/avatars/commerces/{$commerce->id}");

    
    $this->assertEquals($expectedAvatarUrl, $commerce->avatar_url);
});

/**
 * Test para verificar que un usuario puede subir y asignar un nuevo avatar.
 */
it('allows a user to upload and assign a new avatar', function () {
    
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    
    $newAvatar = UploadedFile::fake()->image('new_avatar.jpg');

    
    $response = $this->postJson('/api/user/upload-avatar', [
        'avatar' => $newAvatar,
    ]);

    
    $response->assertStatus(200);

    
    $savedAvatarPath = "avatars/users/{$user->id}";

    
    Storage::disk('public')->assertExists($savedAvatarPath);

    
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

    
    $expectedAvatarUrl = 'https://ui-avatars.com/api/?name=' . $initials . '&color=7F9CF5&background=EBF4FF';
    
    $this->assertEquals($expectedAvatarUrl, $user->profile_photo_url);
});

