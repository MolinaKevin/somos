<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\PointsPurchase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);


it('allows an authenticated user to view their point purchases', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $pointPurchases = PointsPurchase::factory()->count(3)->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/point-purchases");

    
    $response->assertStatus(200);

    
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($pointPurchases as $pointPurchase) {
        $response->assertJsonFragment(['id' => $pointPurchase->id]);
    }
});


it('allows filtering point purchases by date', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $purchase1 = PointsPurchase::factory()->create(['user_id' => $user->id, 'commerce_id' => $commerce->id, 'created_at' => now()->subDays(10)]);
    $purchase2 = PointsPurchase::factory()->create(['user_id' => $user->id, 'commerce_id' => $commerce->id, 'created_at' => now()->subDays(5)]);
    $purchase3 = PointsPurchase::factory()->create(['user_id' => $user->id, 'commerce_id' => $commerce->id, 'created_at' => now()->subDays(2)]);

    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/point-purchases?start_date=".now()->subDays(6)->toDateString()."&end_date=".now()->subDays(4)->toDateString());

    $response->assertStatus(200);

    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($purchase2->id, $responseData[0]['id']);
});


it('allows paginating the point purchases list', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $pointPurchases = PointsPurchase::factory()->count(15)->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/point-purchases?per_page=5");

    
    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  
});


it('allows an authenticated user to delete a point purchase', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $pointPurchase = PointsPurchase::factory()->create([
        'user_id' => $user->id,
        'commerce_id' => $commerce->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->deleteJson("/api/user/point-purchases/{$pointPurchase->id}");

    
    $response->assertStatus(200);

    
    $this->assertDatabaseMissing('points_purchases', [
        'id' => $pointPurchase->id,
    ]);
});

