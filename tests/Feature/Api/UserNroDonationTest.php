<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Donation;
use App\Models\Nro;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);



it('allows an authenticated nro to view received donations', function () {
    
    $user = User::factory()->create();

    
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    
    $commerce = Commerce::factory()->create();

    
    $donations = Donation::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/nros/{$nro->id}/donations");

    
    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($donations as $donation) {
        $response->assertJsonFragment(['id' => $donation->id]);
    }
});



it('allows an authenticated nro to view a specific donation', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    $commerce = Commerce::factory()->create();
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/nros/{$nro->id}/donations/{$donation->id}");

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $donation->id]);
});


it('prevents an nro from viewing donations of another nro', function () {
    
    $nro1 = Nro::factory()->create();
    $nro2 = Nro::factory()->create();

    
    $user = User::factory()->create();
    $nro1->users()->attach($user->id);

    
    $donations = Donation::factory()->count(3)->create([
        'nro_id' => $nro2->id,
    ]);

    
    Sanctum::actingAs($user, ['*']);

    
    $response = $this->getJson("/api/user/nros/{$nro2->id}/donations");

    
    $response->assertStatus(401);
});



it('allows paginating the donations list for nro', function () {
    $user = User::factory()->create();
    $nro = Nro::factory()->create();
    $nro->users()->attach($user->id);

    $commerce = Commerce::factory()->create();
    $donations = Donation::factory()->count(15)->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/nros/{$nro->id}/donations?per_page=5");

    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  
});

