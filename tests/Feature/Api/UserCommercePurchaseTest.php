<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Purchase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);


it('allows an authenticated user to view purchases in a specific commerce', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    $purchases = Purchase::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'user_id' => $user->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/purchases");

    
    $response->assertStatus(200);

    
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($purchases as $purchase) {
        $response->assertJsonFragment(['id' => $purchase->id]);
    }
});


it('prevents a user from viewing purchases in a commerce they do not have access to', function () {
    
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($anotherUser->id);

    
    $purchase = Purchase::factory()->create([
        'commerce_id' => $commerce->id,
        'user_id' => $anotherUser->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/purchases");

    
    $response->assertStatus(401);
});


it('allows filtering purchases by date', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    $commerce->users()->attach($user->id);

    
    $purchase1 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(10)]);
    $purchase2 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(5)]);
    $purchase3 = Purchase::factory()->create(['commerce_id' => $commerce->id, 'user_id' => $user->id, 'created_at' => now()->subDays(2)]);

    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/purchases?start_date=".now()->subDays(6)->toDateString()."&end_date=".now()->subDays(4)->toDateString());

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

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/purchases?per_page=5");

    
    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  
});

