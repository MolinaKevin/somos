<?php

use App\Models\User;
use App\Models\Commerce;
use App\Models\Foto;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('returns all associated commerce for the authenticated user', function () {
    $user = User::factory()->create();

    
    $commerces = Commerce::factory()->count(3)->create();

    
    $user->commerces()->attach($commerces->pluck('id'));

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user/commerces');

    
    $response->assertStatus(200);

    
    $response->assertJsonCount(3, 'data') 
             ->assertJsonFragment(['id' => $commerces[0]->id])
             ->assertJsonFragment(['id' => $commerces[1]->id])
             ->assertJsonFragment(['id' => $commerces[2]->id]);
});

it('can create a new commerce for the authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $commerceData = [
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'opening_time' => '9:00', 
        'closing_time' => '17:30', 
        'latitude' => '50.34677900',
        'longitude' => '50.21799800'
    ];

    $response = $this->post('/api/user/commerces', $commerceData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('commerces', $commerceData);
});

it('can get a specific commerce for the authenticated user', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create([
        'opening_time' => '09:00', 
        'closing_time' => '17:30', 
        'latitude' => '50.34677900', 
        'longitude' => '50.21799800'
    ]);

    $user->commerces()->attach($commerce->id);

    Sanctum::actingAs($user, ['*']);

    $response = $this->get("/api/user/commerces/{$commerce->id}");

    $response->assertStatus(200);
    //$response->assertJson(array_merge($commerce->toArray(), ['commerce' => $commerce->toArray()]));

    $response->assertJson([
        'id' => $commerce->id,
        'donated_points' => 0,
        'gived_points' => 0,
        'created_at' => $commerce->created_at->toISOString(),
        'updated_at' => $commerce->updated_at->toISOString(),
        'name' => $commerce->name,
        'description' => $commerce->description,
        'address' => $commerce->address,
        'city' => $commerce->city,
        'plz' => $commerce->plz,
        'email' => $commerce->email,
        'phone_number' => $commerce->phone_number,
        'website' => $commerce->website,
        'opening_time' => '09:00',
        'closing_time' => '17:30',
        'latitude' => $commerce->latitude,
        'longitude' => $commerce->longitude,
        'points' => $commerce->points,
        'percent' => $commerce->percent,
        'created_at' => $commerce->created_at->toISOString(),
        'updated_at' => $commerce->updated_at->toISOString(),
            //'is_open' => $commerce->is_open,
    ]);

});

it('can update a specific commerce for the authenticated user', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create([]);

    
    $user->commerces()->attach($commerce->id);

    
    Sanctum::actingAs($user, ['*']);

    
    $updatedData = ['name' => 'Updated Commerce Name'];

    
    $response = $this->put("/api/user/commerces/{$commerce->id}", $updatedData);

    
    $response->assertStatus(200);

    
    $updatedCommerce = Commerce::find($commerce->id); 

    
    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'name' => 'Updated Commerce Name',
    ]);
});

it('can delete a specific commerce for the authenticated user', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create([]);
    $user->commerces()->attach($commerce->id);

    Sanctum::actingAs($user, ['*']);

    $response = $this->delete("/api/user/commerces/{$commerce->id}");

    $response->assertStatus(204);
    $this->assertDatabaseMissing('commerces', ['id' => $commerce->id]);
});

it('lists all commerces', function () {
    $user = User::factory()->create();
    $token = $user->createToken('TestToken')->plainTextToken;

    Sanctum::actingAs($user, ['*']);

    $commerces = Commerce::factory()->count(3)->create()->each(function ($commerce) {
    });

    $response = $this->withHeader('Authorization', "Bearer $token")
                     ->getJson('/api/commerces');

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

it('can update the background_image of a specific commerce based on the provided URL for the authenticated user', function () {
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create([]);
    $user->commerces()->attach($commerce->id);

    
    $backgroundImage = UploadedFile::fake()->image('background-image.jpg');

    
    Sanctum::actingAs($user, ['*']);
    $this->postJson("/api/commerces/{$commerce->id}/upload-image", ['foto' => $backgroundImage])
        ->assertStatus(200);

    
    $backgroundImagePath = "fotos/commerces/{$commerce->id}/" . $backgroundImage->hashName();
    $backgroundImageRecord = Foto::where('path', $backgroundImagePath)->first();

    
    $this->assertNotNull($backgroundImageRecord, 'La imagen de fondo no fue encontrada.');

    
    $updatedData = [
        'background_image' => asset('storage/' . $backgroundImageRecord->path),  
    ];

    
    $response = $this->put("/api/user/commerces/{$commerce->id}", $updatedData);

    
    $response->assertStatus(200);

    
    $updatedCommerce = $commerce->fresh(); 

    //dd($updatedCommerce->background_image_id);

    
    $this->assertEquals($backgroundImageRecord->id, $updatedCommerce->background_image_id, 'El background_image_id no se actualizó correctamente.');

    
    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'background_image_id' => $backgroundImageRecord->id,
    ]);

    
    $expectedBackgroundImageUrl = asset('storage/' . $backgroundImageRecord->path);
    $this->assertEquals($expectedBackgroundImageUrl, $updatedCommerce->background_image, 'La URL del background_image no coincide.');
});
