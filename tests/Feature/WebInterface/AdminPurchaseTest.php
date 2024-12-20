<?php

use App\Models\Purchase;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);


it('authenticated user can see purchases list', function () {
    
    $user = User::factory()->create();
    $user2 = User::factory()->create();

    
    $commerce = Commerce::factory()->create();

    
    Purchase::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user2->id,
        'amount' => 10000, 
    ]);

    
    $this->actingAs($user);

    
    $response = $this->get('/admin/purchases');

    
    $response->assertStatus(200);

    
    $response->assertSee('100.00'); 
});


it('can create a purchase', function () {
    
    $admin = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $user = User::factory()->create();

    
    $this->actingAs($admin);

    
    $purchaseData = [
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'amount' => 20000, 
    ];

    
    $response = $this->post('/admin/purchases', $purchaseData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/purchases');

    
    $this->assertDatabaseHas('purchases', [
        'commerce_id' => $commerce->id,
        'amount' => 20000, 
    ]);
});


it('can view a purchase detail', function () {
    
    $admin = User::factory()->create();
    $user = User::factory()->create();
    
    
    $commerce = Commerce::factory()->create();
    $purchase = Purchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'amount' => 20000, 
    ]);

    
    $this->actingAs($admin);

    
    $response = $this->get("/admin/purchases/{$purchase->id}");

    
    $response->assertStatus(200);

    
    $response->assertSee('200.00'); 
});


it('can update a purchase', function () {
    
    $admin = User::factory()->create();

    $user = User::factory()->create();
    
    $commerce = Commerce::factory()->create();
    $purchase = Purchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'amount' => 10000, 
    ]);

    
    $updatedData = [
        'commerce_id' => $commerce->id, 
        'user_id' => $user->id,
        'amount' => 20000, 
    ];

    
    $this->actingAs($admin);

    
    $response = $this->put("/admin/purchases/{$purchase->id}", $updatedData);

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/purchases');

    
    $this->assertDatabaseHas('purchases', [
        'id' => $purchase->id,
        'commerce_id' => $commerce->id,
        'amount' => 20000, 
    ]);
});


it('can delete a purchase', function () {
    
    $commerce = Commerce::factory()->create();
    $user = User::factory()->create();
    $purchase = Purchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
        'amount' => 20000, 
    ]);

    
    $adminUser = User::factory()->create();

    
    $response = $this->actingAs($adminUser)->delete("/admin/purchases/{$purchase->id}");

    
    $response->assertStatus(302);
    $response->assertRedirect('/admin/purchases');

    
    $this->assertDatabaseMissing('purchases', [
        'id' => $purchase->id,
    ]);
});

