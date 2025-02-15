<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Purchase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

it('allows an authenticated user to view referral purchase points', function () {
    
    $user = User::factory()->create();
    $referredUser = User::factory()->create(['referrer_pass' => $user->pass]);

    
    $commerce = Commerce::factory()->create();

    
    $purchase = Purchase::factory()->for($referredUser)->for($commerce)->create();

    
    $purchase->distributePoints();
    
    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/referral-points');

    
    $response->assertStatus(200);

    
    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($purchase->id, $responseData[0]['purchase_id']);
});



it('does not show non-referral purchase points', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $purchase = Purchase::factory()->for($user)->for($commerce)->create();
    $purchase->pointsDistribution()->create([
        'user_id' => $user->id,
        'points' => 100,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/referral-points');

    
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(0, $responseData);
});



it('shows multiple referral purchase points for authenticated user', function () {
    
    $referrer = User::factory()->create();
    $referredUser1 = User::factory()->create(['referrer_pass' => $referrer->pass]);
    $referredUser2 = User::factory()->create(['referrer_pass' => $referrer->pass]);

    
    $commerce = Commerce::factory()->create();
    $purchase1 = Purchase::factory()->for($referredUser1)->for($commerce)->create(['amount' => 1000]);
    $purchase2 = Purchase::factory()->for($referredUser2)->for($commerce)->create(['amount' => 2000]);

    
    \DB::table('purchase_user_points')->insert([
        ['purchase_id' => $purchase1->id, 'user_id' => $referrer->id, 'points' => 50],
        ['purchase_id' => $purchase2->id, 'user_id' => $referrer->id, 'points' => 100],
    ]);

    
    Sanctum::actingAs($referrer, ['*']);

    
    $response = $this->getJson('/api/user/referral-points');

    
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(2, $responseData);
    $this->assertEquals(50, $responseData[0]['points']);
    $this->assertEquals(100, $responseData[1]['points']);
});

it('excludes direct purchase points from the authenticated user and includes only referral points', function () {
    
    $user = User::factory()->create();
    $firstLevelReferral = User::factory()->create(['referrer_pass' => $user->pass]);
    $secondLevelReferral = User::factory()->create(['referrer_pass' => $firstLevelReferral->pass]);

    
    $commerce = Commerce::factory()->create();

    
    $userPurchase = Purchase::factory()->for($user)->for($commerce)->create();
    $purchaseFirstLevel = Purchase::factory()->for($firstLevelReferral)->for($commerce)->create();
    $purchaseSecondLevel = Purchase::factory()->for($secondLevelReferral)->for($commerce)->create();

    
    $userPurchase->distributePoints();
    $purchaseFirstLevel->distributePoints();
    $purchaseSecondLevel->distributePoints();

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/referral-points');

    
    $response->assertStatus(200);

    
    $responseData = $response->json('data');

    
    $purchaseIds = collect($responseData)->pluck('purchase_id');
    $this->assertFalse($purchaseIds->contains($userPurchase->id));

    
    $this->assertTrue($purchaseIds->contains($purchaseSecondLevel->id));
    $this->assertTrue($purchaseIds->contains($purchaseFirstLevel->id));
});


it('returns no data if there are no referral points generated', function () {
    $user = User::factory()->create();

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/referral-points');

    
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(0, $responseData);
});

it('only includes points from referrals up to the 7th level and excludes the user\'s own purchases', function () {
    
    $user = User::factory()->create();

    
    $previousUser = $user;
    $referralUsers = [];
    for ($level = 1; $level <= 10; $level++) {
        $referredUser = User::factory()->create(['referrer_pass' => $previousUser->pass]);
        $referralUsers[$level] = $referredUser;
        $previousUser = $referredUser;
    }

    
    $commerce = Commerce::factory()->create();
    $purchases = [];
    foreach ($referralUsers as $level => $referralUser) {
        $purchase = Purchase::factory()->for($referralUser)->for($commerce)->create();
        $purchase->distributePoints(); 
        $purchases[$level] = $purchase;
    }

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson('/api/user/referral-points');

    
    $response->assertStatus(200);

    
    $responseData = $response->json('data');

    
    $expectedPurchaseIds = collect($purchases)->slice(0, 7)->pluck('id');

    $returnedPurchaseIds = collect($responseData)->pluck('purchase_id')->unique();
    $this->assertEquals($expectedPurchaseIds->sort()->values()->all(), $returnedPurchaseIds->sort()->values()->all());
});

