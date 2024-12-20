<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Category;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('verifies that a commerce appears in the users list of commerces', function () {
    $user = User::factory()->create();
    $data = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'commerce@example.com',
    ];

    $commerce = Commerce::factory()->create($data);
    $commerce->users()->attach($user->id);
    $userCommerces = $user->commerces;
    $this->assertTrue($userCommerces->contains($commerce));
    $this->assertCount(1, $userCommerces);
});

it('verifies that a commerce appears in the user\'s list of commerces via API', function () {
    $user = User::factory()->create();
    $data = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'commerce@example.com',
    ];

    $commerce = Commerce::factory()->create($data);
    $commerce->users()->attach($user->id);
    Sanctum::actingAs($user, ['*']);
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $commerce->id]);
    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
});

it('verifies that creating a commerce', function () {
    $data = [
        'donated_points' => 10,
        'gived_points' => 20,
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'commerce@example.com',
    ];

    $commerce = Commerce::factory()->create($data);
    $this->assertNotNull($commerce);
    $this->assertEquals('Test Commerce', $commerce->name);
    $this->assertEquals('123 Street', $commerce->address);
    $this->assertEquals('City', $commerce->city);
    $this->assertEquals('12345', $commerce->plz);
    $this->assertEquals('commerce@example.com', $commerce->email);
});

it('returns all associated commerces for the authenticated user', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user, ['*']);
    $commerces = collect(range(1, 3))->map(function ($i) use ($user) {
        $data = [
            'donated_points' => 0,
            'gived_points' => 0,
            'name' => "Test Commerce {$i}",
            'address' => "123 Street {$i}",
            'city' => "City {$i}",
            'plz' => "1234{$i}",
            'email' => "commerce{$i}@example.com",
        ];
        $commerce = Commerce::factory()->create($data);
        $response = $this->postJson("/api/commerces/{$commerce->id}/associate", ['user_id' => $user->id]);
        $response->assertStatus(200);
        return $commerce;
    });

    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);

    foreach ($commerces as $commerce) {
        $response->assertJsonFragment(['id' => $commerce->id]);
    }
});

it('allows a user to have multiple associated commerces', function () {
    $user = User::factory()->create();
    $data1 = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'First Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'first@example.com',
    ];
    $commerce1 = Commerce::factory()->create($data1);

    $data2 = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'Second Commerce',
        'address' => '456 Street',
        'city' => 'Another City',
        'plz' => '67890',
        'email' => 'second@example.com',
    ];
    $commerce2 = Commerce::factory()->create($data2);

    $commerce1->users()->attach($user->id);
    $commerce2->users()->attach($user->id);
    $userCommerces = $user->commerces;
    $this->assertTrue($userCommerces->contains($commerce1));
    $this->assertTrue($userCommerces->contains($commerce2));
    $this->assertCount(2, $userCommerces);
});

it('allows a user to have multiple associated commerces via API', function () {
    $user = User::factory()->create();
    $data1 = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'First Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'first@example.com',
    ];
    $commerce1 = Commerce::factory()->create($data1);

    $data2 = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'Second Commerce',
        'address' => '456 Street',
        'city' => 'Another City',
        'plz' => '67890',
        'email' => 'second@example.com',
    ];
    $commerce2 = Commerce::factory()->create($data2);

    Sanctum::actingAs($user, ['*']);
    $this->postJson("/api/commerces/{$commerce1->id}/associate", ['user_id' => $user->id])->assertStatus(200);
    $this->postJson("/api/commerces/{$commerce2->id}/associate", ['user_id' => $user->id])->assertStatus(200);

    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);

    $commerceIds = array_column($responseData, 'id');
    $this->assertContains($commerce1->id, $commerceIds);
    $this->assertContains($commerce2->id, $commerceIds);
});

it('allows a user to associate their commerce with another user', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $data = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'commerce@example.com',
    ];
    $commerce = Commerce::factory()->create($data);
    $commerce->users()->attach($user1->id);

    $this->assertDatabaseHas('commerce_user', [
        'user_id' => $user1->id,
        'commerce_id' => $commerce->id,
    ]);

    Sanctum::actingAs($user2, ['*']);
    $this->postJson("/api/commerces/{$commerce->id}/associate", ['user_id' => $user2->id])->assertStatus(200);

    $this->assertDatabaseHas('commerce_user', [
        'user_id' => $user2->id,
        'commerce_id' => $commerce->id,
    ]);
    $this->assertDatabaseHas('commerce_user', [
        'user_id' => $user1->id,
        'commerce_id' => $commerce->id,
    ]);
});

it('associates commerces to users and verifies visibility based on user context', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $data1 = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'Test Commerce 1',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'commerce1@example.com',
    ];
    $commerce1 = Commerce::factory()->create($data1);

    $data2 = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'Test Commerce 2',
        'address' => '456 Street',
        'city' => 'Another City',
        'plz' => '67890',
        'email' => 'commerce2@example.com',
    ];
    $commerce2 = Commerce::factory()->create($data2);

    $commerce1->users()->attach($user1->id);
    $commerce2->users()->attach($user2->id);

    Sanctum::actingAs($user1, ['*']);
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($commerce1->id, $responseData[0]['id']);
    $this->assertNotEquals($commerce2->id, $responseData[0]['id']);

    Sanctum::actingAs($user2, ['*']);
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($commerce2->id, $responseData[0]['id']);
    $this->assertNotEquals($commerce1->id, $responseData[0]['id']);

    Sanctum::actingAs($user2, ['*']);
    $this->postJson("/api/commerces/{$commerce2->id}/associate", ['user_id' => $user1->id])->assertStatus(200);

    Sanctum::actingAs($user1, ['*']);
    $response = $this->getJson('/api/user/commerces');
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);

    $commerceIds = array_column($responseData, 'id');
    $this->assertContains($commerce1->id, $commerceIds);
    $this->assertContains($commerce2->id, $commerceIds);
});

it('verifies that a newly created commerce has the active attribute set to false', function () {
    $data = [
        'donated_points' => 0,
        'gived_points' => 0,
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'commerce@example.com',
    ];

    $commerce = Commerce::factory()->create($data);
    $this->assertFalse($commerce->active);
});

it('verifies that a newly created commerce has the accepted attribute set to false', function () {
    $data = [
        'name' => 'Test Commerce',
        'address' => '123 Street',
        'city' => 'City',
        'plz' => '12345',
        'email' => 'commerce@example.com',
    ];

    $commerce = Commerce::factory()->create($data);
    $this->assertFalse($commerce->accepted);
});

it('deactivates a commerce when it is unaccepted', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create([
        'active' => true,
        'accepted' => true,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/commerces/{$commerce->id}/unaccept");
    $response->assertStatus(200);
    $this->assertFalse($commerce->fresh()->accepted);
    $this->assertFalse($commerce->fresh()->active);
});

it('authenticated user can create commerce', function () {
    $user = User::factory()->create();
    $data = [
        'name' => 'Test Commerce',
        'description' => 'Descripción del comercio',
        'address' => '123 Calle',
        'city' => 'Ciudad',
        'plz' => '12345',
        'email' => 'commerce@example.com',
        'phone_number' => '+1-123-456-7890',
        'website' => 'https://commerce.example.com',
        'opening_time' => '9:00',
        'closing_time' => '18:00',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'percent' => 10.5,
        'somos_id' => 1,
    ];

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson('/api/user/commerces', $data);
    $response->assertStatus(201);
    $this->assertDatabaseHas('commerces', [
        'name' => 'Test Commerce',
        'email' => 'commerce@example.com',
    ]);
});

it('authenticated user can get commerce list', function () {
    $user = User::factory()->create();
    $commerces = Commerce::factory()->count(3)->create();

    
    $commerces->each(function ($commerce) use ($user) {
        $commerce->users()->attach($user->id);
    });

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson('/api/user/commerces');

    $response->assertStatus(200);

    
    $response->assertJsonCount(3, 'data');
});

it('authenticated user can get specific commerce', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}");

    
    $response->assertStatus(200);

    
    $response->assertJsonFragment([
        'id' => $commerce->id,
        'name' => $commerce->name,
    ]);
});

it('authenticated user can update commerce', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    $data = ['name' => 'Updated Commerce Name'];

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->putJson("/api/user/commerces/{$commerce->id}", $data);

    
    $response->assertStatus(200);

    
    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'name' => 'Updated Commerce Name',
    ]);
});

it('authenticated user can delete commerce', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->deleteJson("/api/user/commerces/{$commerce->id}");

    
    $response->assertStatus(204);

    
    $this->assertDatabaseMissing('commerces', [
        'id' => $commerce->id,
    ]);
});

it('allows activating a commerce', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create([
        'active' => false,
        'accepted' => true,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/user/commerces/{$commerce->id}/activate");
    $response->assertStatus(200);
    $this->assertTrue($commerce->fresh()->active);
});

it('allows deactivating a commerce', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create([
        'active' => true,
        'accepted' => true,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/user/commerces/{$commerce->id}/deactivate");
    $response->assertStatus(200);
    $this->assertFalse($commerce->fresh()->active);
});

it('allows accepting a commerce', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create([
        'accepted' => false,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/commerces/{$commerce->id}/accept");
    $response->assertStatus(200);
    $this->assertTrue($commerce->fresh()->accepted);
});

it('allows unaccepting a commerce and deactivates it automatically', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create([
        'active' => true,
        'accepted' => true,
    ]);

    Sanctum::actingAs($user, ['*']);
    $response = $this->postJson("/api/commerces/{$commerce->id}/unaccept");
    $response->assertStatus(200);
    $this->assertFalse($commerce->fresh()->accepted);
    $this->assertFalse($commerce->fresh()->active);
});

it('updates a commerce and its associated categories', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    $categories = Category::factory()->count(3)->create();

    
    $updatedData = [
        'name' => 'Updated Commerce Name',
        'address' => 'Updated Address',
        'city' => 'Updated City',
        'plz' => '12345',
        'latitude' => '50.123456',
        'longitude' => '8.123456',
        'opening_time' => '8:00',
        'closing_time' => '20:00',
        'categories' => $categories->pluck('id')->toArray(),
    ];

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->putJson("/api/user/commerces/{$commerce->id}", $updatedData);

    
    $response->assertStatus(200);

    
    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'name' => $updatedData['name'],
        'address' => $updatedData['address'],
        'city' => $updatedData['city'],
        'plz' => $updatedData['plz'],
        'latitude' => $updatedData['latitude'],
        'longitude' => $updatedData['longitude'],
        'opening_time' => $updatedData['opening_time'],
        'closing_time' => $updatedData['closing_time'],
    ]);

    
    $this->assertEquals(
        $categories->pluck('id')->toArray(),
        $commerce->categories->pluck('id')->toArray()
    );

});

it('retrieves a commerce with associated categories as a list of IDs', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    $categories = Category::factory()->count(3)->create();
    $commerce->categories()->attach($categories->pluck('id')->toArray());

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}");

    
    $response->assertStatus(200);

    
    $responseData = $response->json();

    
    $this->assertEquals($commerce->id, $responseData['id']);
    $this->assertEquals($commerce->name, $responseData['name']);

    
    $this->assertArrayHasKey('category_ids', $responseData);

    
    $expectedCategoryIds = $categories->pluck('id')->toArray();
    sort($expectedCategoryIds);

    $responseCategoryIds = $responseData['category_ids'];
    sort($responseCategoryIds);

    $this->assertEquals($expectedCategoryIds, $responseCategoryIds);
});

