<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('user can get points', function () {

	$points = 2000;
    $giver = User::factory()->create(['points' => $points*2]);
	$token = $giver->createToken('login')->plainTextToken;

	$user = User::factory()->create();
	
	$this->assertDatabaseHas('users', [
		'name' => $user->name,
		'points' => 0
	]);

	Sanctum::actingAs(
        $giver,
        ['*']
    );

	if (!$giver) {
		return response()->json(['error' => 'Unauthenticated'], 401);
	}

	$response = $this->withHeaders([
		'Authorization' => 'Bearer '.$token,
	])->postJson('/api/points/give', [
		'points' => $points,
		'receiver_id' => $user->id
	]);

	$this->assertDatabaseHas('users', [
		'name' => $user->name,
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
