<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Cashout;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);


it('allows an authenticated user to view cashouts in a specific commerce', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    $cashouts = Cashout::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/cashouts");

    
    $response->assertStatus(200);

    
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($cashouts as $cashout) {
        $response->assertJsonFragment(['id' => $cashout->id]);
    }
});


it('prevents a user from viewing cashouts in a commerce they do not have access to', function () {
    
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($anotherUser->id);

    
    $cashout = Cashout::factory()->create([
        'commerce_id' => $commerce->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/cashouts");

    
    $response->assertStatus(401);
});


it('allows filtering cashouts by date', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    $commerce->users()->attach($user->id);

    
    $cashout1 = Cashout::factory()->create(['commerce_id' => $commerce->id, 'created_at' => now()->subDays(10)]);
    $cashout2 = Cashout::factory()->create(['commerce_id' => $commerce->id, 'created_at' => now()->subDays(5)]);
    $cashout3 = Cashout::factory()->create(['commerce_id' => $commerce->id, 'created_at' => now()->subDays(2)]);

    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/cashouts?start_date=".now()->subDays(6)->toDateString()."&end_date=".now()->subDays(4)->toDateString());

    $response->assertStatus(200);

    $responseData = $response->json('data');
    $this->assertCount(1, $responseData);
    $this->assertEquals($cashout2->id, $responseData[0]['id']);
});


it('allows paginating the cashouts list', function () {
    
    $user = User::factory()->create();

    
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    
    $cashouts = Cashout::factory()->count(15)->create([
        'commerce_id' => $commerce->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/commerces/{$commerce->id}/cashouts?per_page=5");

    
    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  
});

