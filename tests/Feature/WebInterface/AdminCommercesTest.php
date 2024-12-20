<?php

use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('authenticated user can see commerces list', function () {
    
    $user = User::factory()->create();

    
    Commerce::factory()->count(3)->create([
        'name' => 'Test Commerce',
        'city' => 'Test City',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true
    ]);

    
    $this->actingAs($user);

    
    $response = $this->get('/admin/commerces');

    
    $response->assertStatus(200);

    
    $response->assertSee('Test Commerce');
    $response->assertSee('Test City');
    $response->assertSee('1234567890');
});


it('can create a commerce', function () {
    
    $admin = User::factory()->create();

    
    $this->actingAs($admin);

    
    $commerceData = [
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true,
    ];

    
    $response = $this->post('/admin/commerces', $commerceData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/commerces');

    
    $this->assertDatabaseHas('commerces', [
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true,
    ]);
});


it('can view a commerce detail', function () {
    
    $admin = User::factory()->create();

    
    $commerce = Commerce::factory()->create([
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true,
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/commerces/{$commerce->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee('Test Commerce');
    $response->assertSee('testcommerce@example.com');
    $response->assertSee('1234567890');
    $response->assertSee('Test City');
});


it('can update a commerce', function () {
    
    $admin = User::factory()->create();

    
    $commerce = Commerce::factory()->create([
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
        'phone_number' => '1234567890',
        'city' => 'Test City',
        'latitude' => 40.7128,
        'longitude' => -74.0060,
        'points' => 100,
        'active' => true,
        'accepted' => true,
    ]);

    
    $updatedData = [
        'name' => 'Updated Commerce',
        'email' => 'updatedcommerce@example.com',
        'phone_number' => '0987654321',
        'city' => 'Updated City',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
        'points' => 200,
        'active' => false,
        'accepted' => false,
    ];

    
    $this->actingAs($admin);

    
    $response = $this->put("/admin/commerces/{$commerce->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/commerces');

    
    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'name' => 'Updated Commerce',
        'email' => 'updatedcommerce@example.com',
        'phone_number' => '0987654321',
        'city' => 'Updated City',
        'latitude' => 51.5074,
        'longitude' => -0.1278,
        'points' => 200,
        'active' => false,
        'accepted' => false,
    ]);
});


it('can delete a commerce', function () {
    
    $commerce = Commerce::factory()->create([
        'name' => 'Test Commerce',
        'email' => 'testcommerce@example.com',
    ]);

    
    $adminUser = User::factory()->create([
        'email' => 'admin@example.com',
    ]);

    
    $response = $this->actingAs($adminUser)->delete("/admin/commerces/{$commerce->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/commerces');

    
    $this->assertDatabaseMissing('commerces', [
        'id' => $commerce->id,
    ]);
});

