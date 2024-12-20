<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Purchase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);


it('allows an authenticated user to view their purchases', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $purchases = Purchase::factory()->count(3)->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/purchases");

    
    $response->assertStatus(200);

    
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($purchases as $purchase) {
        $response->assertJsonFragment(['id' => $purchase->id]);
    }
});

it('allows filtering purchases by date', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    $commerce->users()->attach($user->id);

    
    $purchase1 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(10)]);
    $purchase2 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(5)]);
    $purchase3 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(2)]);

    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/purchases?start_date=".now()->subDays(6)->toDateString()."&end_date=".now()->subDays(4)->toDateString());

    $response->assertStatus(200);

    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($purchase2->id, $responseData[0]['id']);
});

it('allows paginating the purchases list', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    $purchases = Purchase::factory()->count(15)->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/purchases?per_page=5");

    
    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  
});



it('allows an authenticated user to delete a purchase', function () {
    
    $user = User::factory()->create();

    
    $purchase = Purchase::factory()->create([
        'user_id' => $user->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->deleteJson("/api/user/purchases/{$purchase->id}");

    
    $response->assertStatus(200);

    
    $this->assertDatabaseMissing('purchases', [
        'id' => $purchase->id,
    ]);
});


it('includes user points received for each purchase', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $purchases = Purchase::factory()->count(3)->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    
    foreach ($purchases as $purchase) {
        \DB::table('purchase_user_points')->insert([
            'purchase_id' => $purchase->id,
            'user_id' => $user->id,
            'points' => 100,  
        ]);
    }

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/purchases");

    
    $response->assertStatus(200);

    
    $responseData = $response->json('data');
    foreach ($responseData as $purchaseData) {
        $this->assertArrayHasKey('user_points_received', $purchaseData);
        $this->assertEquals(100, $purchaseData['user_points_received']); 
    }
});

