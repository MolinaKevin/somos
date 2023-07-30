<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('user can give and get points', function () {

	$points = 2000.0;
    $giver = User::factory()->create(['points' => $points*2]);
	$token = $giver->createToken('login')->plainTextToken;

	$receiver = User::factory()->create();
	
	$this->assertDatabaseHas('users', [
		'name' => $receiver->name,
		'points' => 0
	]);

	Sanctum::actingAs(
        $giver,
        ['*']
    );

	$response = $this->withHeaders([
		'Authorization' => 'Bearer '.$token,
	])->postJson('/api/points/give', [
		'points' => $points,
		'receiver_id' => $receiver->id
	]);

	$this->assertDatabaseHas('users', [
		'name' => $receiver->name,
		'points' => $points 
	]);

    $response->assertStatus(200);

	// Recargar los modelos de usuario
    $giver->refresh();
    $receiver->refresh();

    // Asegurarse de que los puntos fueron transferidos correctamente
    expect($giver->points)->toBe($points);
    expect($receiver->points)->toBe($points);
});

test('user get unauthenticated', function () {

	$points = 2000.0;
    $giver = User::factory()->create(['points' => $points*2]);

	$receiver = User::factory()->create();
	
	$this->assertDatabaseHas('users', [
		'name' => $receiver->name,
		'points' => 0
	]);

	Sanctum::actingAs(
        $giver,
        ['*']
    );
	
	// Post without Token
	$response = $this->postJson('/api/points/give', [
		'points' => $points,
		'receiver_id' => $receiver->id
	]);

	$this->assertDatabaseHas('users', [
		'name' => $receiver->name,
		'points' => 0 
	]);

	$response->assertJson([
        'error' => 'Not authenticated.'
    ]);

    $response->assertStatus(401);

	// Recargar los modelos de usuario
    $giver->refresh();
    $receiver->refresh();

    // Asegurarse de que los puntos no fueron transferidos 
    expect($giver->points)->toBe($points*2);
    expect($receiver->points)->toBe(0.0);
});

test('user cannot give more points as he have', function () {

	$points = 2000.0;
    $giver = User::factory()->create(['points' => $points/2]);
	$token = $giver->createToken('login')->plainTextToken;

	$receiver = User::factory()->create();
	
	$this->assertDatabaseHas('users', [
		'name' => $receiver->name,
		'points' => 0
	]);

	Sanctum::actingAs(
        $giver,
        ['*']
    );
	
	$response = $this->withHeaders([
		'Authorization' => 'Bearer '.$token,
	])->postJson('/api/points/give', [
		'points' => $points,
		'receiver_id' => $receiver->id
	]);

	$this->assertDatabaseHas('users', [
		'name' => $receiver->name,
		'points' => 0 
	]);

	$response->assertJson([
        'error' => 'Not enough points to give.'
    ]);

    $response->assertStatus(402);

	// Recargar los modelos de usuario
    $giver->refresh();
    $receiver->refresh();

    // Asegurarse de que los puntos no fueron transferidos 
    expect($giver->points)->toBe($points/2);
    expect($receiver->points)->toBe(0.0);
});
