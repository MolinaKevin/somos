<?php

use App\Models\PointsPurchase;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('authenticated user can see points /pointsPurchases list', function () {
    
    $user = User::factory()->create();
    $user2 = User::factory()->create();

    
    $commerce = Commerce::factory()->create();

    
    PointsPurchase::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user2->id,
        'points' => 100, 
    ]);

    
    $this->actingAs($user);

    
    $response = $this->get('/admin/pointsPurchases');

    
    $response->assertStatus(200);

    
    $response->assertSee('100'); 
});


it('can create a points purchase', function () {
    
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $user = User::factory()->create(['points' => 300]); 

    
    $this->actingAs($admin);

    
    $pointsPurchasesData = [
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'points' => 200, 
    ];

    
    $response = $this->post('/admin/pointsPurchases', $pointsPurchasesData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/pointsPurchases');

    
    $this->assertDatabaseHas('points_purchases', [
        'commerce_id' => $commerce->id,
        'points' => 200, 
    ]);
});


it('can view a points purchase detail', function () {
    
    $admin = User::factory()->create();
    $user = User::factory()->create(['points' => 300]); 
    
    
    $commerce = Commerce::factory()->create();
    $pointsPurchases = PointsPurchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'points' => 200, 
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/pointsPurchases/{$pointsPurchases->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee('200'); 
});


it('can update a points purchase', function () {
    
    $admin = User::factory()->create();

    $user = User::factory()->create(['points' => 300]); 
    
    $commerce = Commerce::factory()->create();
    $pointsPurchases = PointsPurchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'points' => 100, 
    ]);

    
    $updatedData = [
        'commerce_id' => $commerce->id, 
        'user_id' => $user->id,
        'points' => 200, 
    ];

    
    $this->actingAs($admin);

    
    $response = $this->put("/admin/pointsPurchases/{$pointsPurchases->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/pointsPurchases');

    
    $this->assertDatabaseHas('points_purchases', [
        'id' => $pointsPurchases->id,
        'commerce_id' => $commerce->id,
        'points' => 200, 
    ]);
});


it('can delete a points purchase', function () {
    
    $commerce = Commerce::factory()->create();
    $user = User::factory()->create(['points' => 300]); 
    $pointsPurchases = PointsPurchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'points' => 200, 
    ]);

    
    $adminUser = User::factory()->create();

    
    $response = $this->actingAs($adminUser)->delete("/admin/pointsPurchases/{$pointsPurchases->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/pointsPurchases');

    
    $this->assertDatabaseMissing('points_purchases', [
        'id' => $pointsPurchases->id,
    ]);
});

