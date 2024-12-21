<?php

use App\Models\User;
use App\Models\Commerce;
use App\Models\Foto;
use App\Models\Category;
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

    
    $this->assertEquals($backgroundImageRecord->id, $updatedCommerce->background_image_id, 'El background_image_id no se actualizó correctamente.');

    
    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'background_image_id' => $backgroundImageRecord->id,
    ]);

    
    $expectedBackgroundImageUrl = asset('storage/' . $backgroundImageRecord->path);
    $this->assertEquals($expectedBackgroundImageUrl, $updatedCommerce->background_image, 'La URL del background_image no coincide.');
});

it('can assign a main category when creating a new commerce', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $categories = Category::factory()->count(3)->create();

    $mainCategory = $categories->first();

    $commerceData = [
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'Göttingen',
        'plz' => '37075',
        'opening_time' => '7:53', 
        'closing_time' => '21:00',
        'latitude' => '51.53636134',
        'longitude' => '9.91903678',
        'percent' => 10.0,
        'category_id' => $mainCategory->id, 
        'categories' => [$categories[0]->id, $categories[1]->id, $categories[2]->id]
    ];

    $response = $this->postJson('/api/user/commerces', $commerceData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('commerces', [
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'Göttingen',
        'plz' => '37075',
        'opening_time' => '07:53:00', 
        'closing_time' => '21:00:00',
        'latitude' => '51.53636134',
        'longitude' => '9.91903678',
        'percent' => 10.0,
        'category_id' => $mainCategory->id,
    ]);

    $commerce = Commerce::where('name', 'Test Commerce')->first();

    expect($commerce->categories)->toHaveCount(3);
    expect($commerce->categories->pluck('id'))->toContain($categories[0]->id)
         ->and($commerce->categories->pluck('id'))->toContain($categories[1]->id)
         ->and($commerce->categories->pluck('id'))->toContain($categories[2]->id);

    expect($commerce->category_id)->toBe($mainCategory->id);
});

it('can update the main category of a commerce', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $categories = Category::factory()->count(3)->create();

    $commerce = Commerce::factory()->create([
        'category_id' => $categories[0]->id, 
    ]);
    $commerce->categories()->attach([$categories[0]->id, $categories[1]->id]);

    $updateData = [
        'category_id' => $categories[1]->id, 
    ];

    $response = $this->putJson("/api/user/commerces/{$commerce->id}", $updateData);

    $response->assertStatus(200);

    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'category_id' => $categories[1]->id,
    ]);

    $updatedCommerce = $commerce->fresh();

    expect($updatedCommerce->category_id)->toBe($categories[1]->id);

    expect($updatedCommerce->categories->pluck('id'))->toContain($categories[1]->id);
});

it('can create a commerce without assigning a main category', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);

    $categories = Category::factory()->count(3)->create();

    $commerceData = [
        'name' => 'No Main Category Commerce',
        'address' => '456 Avenue',
        'city' => 'Berlin',
        'plz' => '10115',
        'opening_time' => '7:53',
        'closing_time' => '21:00',
        'latitude' => '52.5200',
        'longitude' => '13.4050',
        'percent' => 5.0,
        'categories' => [$categories[0]->id, $categories[1]->id],
    ];

    $response = $this->postJson('/api/user/commerces', $commerceData);

    $response->assertStatus(201);

    $this->assertDatabaseHas('commerces', [
        'name' => 'No Main Category Commerce',
        'category_id' => null,
    ]);

    $commerce = Commerce::where('name', 'No Main Category Commerce')->first();

    expect($commerce->categories)->toHaveCount(2);
    expect($commerce->categories->pluck('id'))->toContain($categories[0]->id)
                                             ->and($commerce->categories->pluck('id'))->toContain($categories[1]->id);

    expect($commerce->category_id)->toBeNull();
});

