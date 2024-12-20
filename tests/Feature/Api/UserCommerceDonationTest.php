<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\Donation;
use App\Models\Nro;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);


it('allows an authenticated commerce to view donations', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    $nro = Nro::factory()->create();
    $donations = Donation::factory()->count(3)->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/commerces/{$commerce->id}/donations");

    $response->assertStatus(200);
    $responseData = $response->json('data');
    $this->assertCount(3, $responseData);
    foreach ($donations as $donation) {
        $response->assertJsonFragment(['id' => $donation->id]);
    }
});


it('allows an authenticated commerce to view a specific donation', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    $nro = Nro::factory()->create();
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/commerces/{$commerce->id}/donations/{$donation->id}");

    $response->assertStatus(200);
    $response->assertJsonFragment(['id' => $donation->id]);
});


it('prevents a commerce from viewing donations in a commerce they do not have access to', function () {
    $user = User::factory()->create();
    $anotherUser = User::factory()->create();

    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($anotherUser->id);

    $nro = Nro::factory()->create();
    $donation = Donation::factory()->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/commerces/{$commerce->id}/donations");

    $response->assertStatus(401);
});


it('allows paginating the donations list', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $commerce->users()->attach($user->id);

    $nro = Nro::factory()->create();
    $donations = Donation::factory()->count(15)->create([
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
    ]);

    Sanctum::actingAs($user, ['*']);

    $response = $this->getJson("/api/user/commerces/{$commerce->id}/donations?per_page=5");

    $response->assertStatus(200);
    $response->assertJsonCount(5, 'data');  
});

