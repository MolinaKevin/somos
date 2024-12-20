<?php

use App\Models\{
    User,
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
    
    
    expect($pointsPurchase->user_id)->toBe($user->id);
    expect($pointsPurchase->commerce_id)->toBe($commerce->id);
});

it('user points decrease after points purchase', function () {
    $user = User::factory()->create(['points' => 500]);

    $commerce = Commerce::factory()->create([
        'points' => 1000
    ]); 

	$pointsPurchase = PointsPurchase::factory()->make(['points' => 100, 'uuid' => (string) Str::uuid()]);
    $pointsPurchase->commerce()->associate($commerce);
    $pointsPurchase->save();

    $this->actingAs($user)
		 ->get(route('pointsPurchase.pay', ['uuid' => $pointsPurchase->uuid]));

    
    expect($user->fresh()->points)->toBe(400.0);
});

it('cannot make points purchase if user has insufficient points', function () {
    $user = User::factory()->create(['points' => 50]);
    $commerce = Commerce::factory()->create();

    $pointsPurchase = PointsPurchase::factory()->make(['points' => 100]);
    $pointsPurchase->commerce()->associate($commerce);
    $pointsPurchase->save();

    
    expect(fn() => $pointsPurchase->payWithPoints($user))
        ->toThrow(InsufficientPointsException::class);
});


it('commerce points increase after points purchase', function () {
  $user = User::factory()->create(['points' => 500]);

  $commerce = Commerce::factory()->create(['points' => 1000]);

  $pointsPurchase = PointsPurchase::factory()->make(['points' => 100, 'uuid' => (string) Str::uuid()]);
  $pointsPurchase->commerce()->associate($commerce);
  $pointsPurchase->save();

  $this->actingAs($user)
       ->get(route('pointsPurchase.pay', ['uuid' => $pointsPurchase->uuid]));

  
  expect($commerce->fresh()->points)->toEqual(1100.0);
});

it('user points decrease after points purchase paid with qr', function () {
  $user = User::factory()->create(['points' => 500]);
  $commerce = Commerce::factory()->create();

  $pointsPurchase = PointsPurchase::factory()->make(['points' => 100, 'uuid' => (string) Str::uuid()]);
  $pointsPurchase->commerce()->associate($commerce);
  $pointsPurchase->save();

  
  //$qrCode = $commerce->createQrPointsCode($pointsPurchase);

  
  $this->actingAs($user)
       ->get(route('pointsPurchase.pay', ['uuid' => $pointsPurchase->uuid]));

  expect($user->fresh()->points)->toBe(400.0);
});

