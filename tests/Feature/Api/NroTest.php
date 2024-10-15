<?php

use App\Models\User;
use App\Models\Nro;
use App\Models\Somos;
use App\Models\Foto;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns all associated nro entities for the authenticated user', function () {
    // Create a user
    $user = User::factory()->create();

    // Create some nro entities and associate them with the user
    $nros = Nro::factory()->count(3)->create();

    // Attach the nros to the user
    $user->nros()->attach($nros->pluck('id'));

    Sanctum::actingAs(
        $user,
        ['*']
    );

    // The API call
    $response = $this->get('/api/user/nros');

    // Assert the response
    $response->assertStatus(200);

    // Get the nros data from the response
    $responseData = $response->json();

    $this->assertNotNull($responseData, 'El campo data no está presente en la respuesta.');
    $response->assertJsonCount(3, 'data') // Verifica que hay 3 comercios
             ->assertJsonFragment(['id' => $nros[0]->id])
             ->assertJsonFragment(['id' => $nros[1]->id])
             ->assertJsonFragment(['id' => $nros[2]->id]);

});

it('can create a new nro for the authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $somos = Somos::factory()->create();

    $nroData = [
        'name' => 'Test Nro',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'opening_time' => '09:00', 
        'closing_time' => '17:30', 
        'latitude' => '50.34677900',
        'longitude' => '50.21799800',
        'somos_id' => $somos->id
    ];

    $response = $this->post('/api/user/nros', $nroData);

    $response->assertStatus(201);
});

it('can get a specific nro for the authenticated user', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $user->nros()->attach($nro->id);

    Sanctum::actingAs($user, ['*']);

    $response = $this->get("/api/user/nros/{$nro->id}");

    $response->assertStatus(200);
    $response->assertJson($nro->toArray());
});

it('can update a specific nro for the authenticated user', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();

    $user->nros()->attach($nro->id);

    Sanctum::actingAs($user, ['*']);

    $updatedData = ['name' => 'Updated Nro Name'];
    $response = $this->put("/api/user/nros/{$nro->id}", $updatedData);

    $response->assertStatus(200);
});

it('can delete a specific nro for the authenticated user', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $user->nros()->attach($nro->id);

    Sanctum::actingAs($user, ['*']);

    $response = $this->delete("/api/user/nros/{$nro->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('nros', ['id' => $nro->id]);
});

it('lists all nros with their entities', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    Sanctum::actingAs($user, ['*']);

    $nros = Nro::factory()->count(3)->create();

    $response = $this->withHeader('Authorization', "Bearer $token")
                     ->getJson('/api/nros');

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     '*' => [
                         'id',
                         'avatar',
                         'background_image',
                         'is_open',
                         'name',
                         'description',
                         'address',
                         'city',
                         'plz',
                         'email',
                         'phone_number',
                         'website',
                         'latitude',
                         'longitude',
                         'points',
                         'percent',
                     ]
                 ]
             ]);
});

it('can create a new nro with an associated entity for the authenticated user', function () {
    // Crear un usuario autenticado
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $somos = Somos::factory()->create();

    $nroData = [
        'somos_id' => $somos->id,
        'contributed_points' => 100,
        'to_contribute' => 50,
        'name' => 'test ent',
        'address' => 'ssseee',
        'city' => 'Luebek',
        'plz' => '37075',
    ];

    $response = $this->post('/api/user/nros', $nroData);

    $response->assertStatus(201);

    $nro = Nro::first();
    $this->assertNotNull($nro);

});

it('can update the background_image of a specific NRO based on the provided URL for the authenticated user', function () {
    $user = User::factory()->create();

    // Crear una NRO asociada al usuario
    $nro = Nro::factory()->create([]);
    $user->nros()->attach($nro->id);

    // Simular archivos de imagen y guardarlos en la tabla de fotos
    $backgroundImage = UploadedFile::fake()->image('background-image.jpg');

    // Subir la imagen simulada
    Sanctum::actingAs($user, ['*']);
    $this->postJson("/api/nros/{$nro->id}/upload-image", ['foto' => $backgroundImage])
        ->assertStatus(200);

    // Recuperar el registro de la imagen subida para simular la URL que enviará el front-end
    $backgroundImagePath = "fotos/nros/{$nro->id}/" . $backgroundImage->hashName();
    $backgroundImageRecord = Foto::where('path', $backgroundImagePath)->first();

    // Verificar que la imagen fue subida correctamente
    $this->assertNotNull($backgroundImageRecord, 'La imagen de fondo no fue encontrada.');

    // Preparar los datos actualizados, incluyendo la URL de la imagen
    $updatedData = [
        'background_image' => asset('storage/' . $backgroundImageRecord->path),  // Simular la URL que se enviaría desde el front
    ];

    // Hacer la solicitud de actualización
    $response = $this->put("/api/user/nros/{$nro->id}", $updatedData);

    // Verificar que la respuesta fue exitosa
    $response->assertStatus(200);

    // Recuperar la NRO actualizada
    $updatedNro = $nro->fresh(); // Refrescar la NRO para obtener los cambios más recientes

    // Verificar que el `background_image_id` fue actualizado correctamente
    $this->assertEquals($backgroundImageRecord->id, $updatedNro->background_image_id, 'El background_image_id no se actualizó correctamente.');

    // Verificar que la base de datos refleja la actualización del `background_image_id`
    $this->assertDatabaseHas('nros', [
        'id' => $nro->id,
        'background_image_id' => $backgroundImageRecord->id,
    ]);

    // Verificar que el background_image URL se genera correctamente
    $expectedBackgroundImageUrl = asset('storage/' . $backgroundImageRecord->path);
    $this->assertEquals($expectedBackgroundImageUrl, $updatedNro->background_image, 'La URL del background_image no coincide.');
});

