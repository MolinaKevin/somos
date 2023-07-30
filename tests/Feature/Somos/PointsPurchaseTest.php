<?php

use App\Models\{
    User,
    Entity,
    Commerce,
    PointsPurchase
};

it('can create a points purchase', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    $pointsPurchase = PointsPurchase::factory()->make();
    $pointsPurchase->user()->associate($user);
    $pointsPurchase->commerce()->associate($commerce);
    $pointsPurchase->save();
    
    // Asserts
    expect($pointsPurchase->user_id)->toBe($user->id);
    expect($pointsPurchase->commerce_id)->toBe($commerce->id);
});

it('user points decrease after points purchase', function () {
    $user = User::factory()->create(['points' => 500]);

    $entity = Entity::factory()->make(['points' => 1000]); // 10% para el ejemplo
    $commerce = Commerce::factory()->create(); 
	$commerce->entity()->save($entity);

	$pointsPurchase = PointsPurchase::factory()->make(['points' => 100, 'uuid' => (string) Str::uuid()]);
    $pointsPurchase->commerce()->associate($commerce);
    $pointsPurchase->save();

    $this->actingAs($user)
		 ->get(route('pointsPurchase.pay', ['uuid' => $pointsPurchase->uuid]));

    // Asserts
    expect($user->fresh()->points)->toBe(400.0);
});

it('cannot make points purchase if user has insufficient points', function () {
    $user = User::factory()->create(['points' => 50]);
    $commerce = Commerce::factory()->create();

    $pointsPurchase = PointsPurchase::factory()->make(['points' => 100]);
    $pointsPurchase->commerce()->associate($commerce);
    $pointsPurchase->save();

    // La siguiente línea debería lanzar una excepción
    expect(fn() => $pointsPurchase->payWithPoints($user))
        ->toThrow(InsufficientPointsException::class);
});


it('commerce points increase after points purchase', function () {
  $user = User::factory()->create(['points' => 500]);

  $entity = Entity::factory()->make(['points' => 1000]); // 10% para el ejemplo
  $commerce = Commerce::factory()->create();
  $commerce->entity()->save($entity);

  $pointsPurchase = PointsPurchase::factory()->make(['points' => 100, 'uuid' => (string) Str::uuid()]);
  $pointsPurchase->commerce()->associate($commerce);
  $pointsPurchase->save();

  $this->actingAs($user)
       ->get(route('pointsPurchase.pay', ['uuid' => $pointsPurchase->uuid]));

  // Asserts
  expect($commerce->fresh()->points)->toBe(1100.0);
});

it('user points decrease after points purchase paid with qr', function () {
  $user = User::factory()->create(['points' => 500]);
  $commerce = Commerce::factory()->create();

  $pointsPurchase = PointsPurchase::factory()->make(['points' => 100, 'uuid' => (string) Str::uuid()]);
  $pointsPurchase->commerce()->associate($commerce);
  $pointsPurchase->save();

  // Create the QR Code for this PointsPurchase
  $qrCode = $commerce->createQrPointsCode($pointsPurchase);

  // Simulate the user scanning the QR Code to pay
  $this->actingAs($user)
       ->get(route('pointsPurchase.pay', ['uuid' => $pointsPurchase->uuid]));

  expect($user->fresh()->points)->toBe(400.0);
});

