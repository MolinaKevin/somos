<?php

use App\Models\{
	User,
	Commerce,
	Purchase,
};
use App\Helpers\ConversionHelper;

use Illuminate\Foundation\Testing\RefreshDatabase;


it('can make a purchase', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();
    $purchase = Purchase::factory()->make();
    
    
    $purchase->user()->associate($user);
    $purchase->commerce()->associate($commerce);
    $purchase->save();
    
    
    expect($purchase->user_id)->toBe($user->id);
    expect($purchase->commerce_id)->toBe($commerce->id);
});

it('calculates points correctly', function () {
    
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($user)->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(10)]);

    
    $purchase->refresh();  
    $points = $purchase->points;

    
    expect($points)->toBe(100.0); 

    expect($purchase->gived_to_users_points)->toEqual(0); 
    expect($purchase->donated_points)->toEqual(0); 
});


it('distributes points correctly among referrers', function () {
    
    $users = User::factory()->count(9)->create();

    for ($i = 0; $i < 8; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]); 
    
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(20)]);

    
    $purchase->distributePoints();

    expect($purchase->fresh()->gived_to_users_points + $purchase->fresh()->donated_points)->toEqual($purchase->fresh()->points);
	
	
	$users = $users->map(function ($user) {
		return $user->fresh();
	});

	$pointsArr = $users->map(function ($user) {
		return $user->points;
	});

    for ($i = 0; $i < 8; $i++) {
		$expected_points = $purchase->points * (0.25) / pow(2, $i);
    }

	
	$remainingPoints = floatval($purchase->points); 
	for ($i = 0; $i < 8; $i++) {
		$remainingPoints -= (floatval($purchase->points) * (0.25 / pow(2, $i)));
	}

	expect($purchase->commerce->fresh()->gived_points)->toEqual(200.0);
	expect($purchase->commerce->fresh()->donated_points)->toEqual($remainingPoints);
});

it('distributes points correctly among incomplete referrers chain', function () {
    
    $users = User::factory()->count(4)->create();

    for ($i = 0; $i < 3; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }
    $commerce = Commerce::factory()->create(['percent' => 10]); 
    
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(20)]);
    
    $purchase->distributePoints();
	expect($purchase->fresh()->gived_to_users_points + $purchase->fresh()->donated_points)->toEqual($purchase->fresh()->points);
    
    $users = $users->map(function ($user) {
        return $user->fresh();
    });

    $pointsArr = $users->map(function ($user) {
        return $user->points;
    });
    for ($i = 0; $i < 3; $i++) {
        $expected_points = $purchase->points * (0.25) / pow(2, $i);
        expect($users[$i]->points)->toEqual($expected_points);
    }
    
	$remainingPoints = floatval($purchase->points); 
	for ($i = 0; $i < count($users); $i++) {
        $remainingPoints -= (floatval($purchase->points) * (0.25 / pow(2, $i)));
    }

	expect($purchase->commerce->fresh()->gived_points)->toEqual(200.0);
    expect($purchase->commerce->fresh()->donated_points)->toEqual($remainingPoints);
    expect($purchase->commerce->fresh()->donated_points)->toEqual(106.25);
});

it('can generate a QR code for payment', function () {
    $user = User::factory()->create(['points' => 500]);
    $commerce = Commerce::factory()->create();
    
    $purchase = Purchase::factory()->make(['amount' => ConversionHelper::moneyToPoints(2000)]);
    $purchase->commerce()->associate($commerce);
    $purchase->save();

    //$commerce->createQrPayCode($purchase);

    
    
    $uuid = $purchase->uuid;
	$url = route('purchase.pay', ['uuid' => $purchase->uuid]);

    
    expect(str_contains($url, $uuid))->toBeTrue();
});

it('distributes points correctly after payment', function () {
	$referrer_test = User::factory()->create(['points' => 225]);
    $user = User::factory()->create(['points' => 500, 'referrer_pass' => $referrer_test->pass]);
    $commerce = Commerce::factory()->create(['percent' => 10]); 
   
    $purchase = Purchase::factory()->make(['amount' => ConversionHelper::moneyToPoints(20)]);
    $purchase->commerce()->associate($commerce);
    $purchase->save();

    
    $response = $this->actingAs($user)
					 ->post(route('purchase.pay', ['uuid' => $purchase->uuid]));

    
    $response->assertStatus(200);

    
    $user->refresh();

    
    
    expect($user->points)->toBe(550.0);

    
    
    $referrer = $user->referrer;
    $referrer->refresh();
    expect($referrer->points)->toBe(250.0);
});

it('can pre-create a purchase', function () {
    $user = User::factory()->create(['points' => 500, 'pass' => 'DE-X88X88X']);
    $commerce = Commerce::factory()->create();

    $response = $this->post(route('preCreatePurchase'), [
        'amount' => ConversionHelper::moneyToPoints(100),
		'userPass' => $user->pass,
        'commerceId' => $commerce->id,
    ]);

    $response->assertStatus(200);

	
    $purchase = Purchase::where([
        'amount' => ConversionHelper::moneyToPoints(100),
        'commerce_id' => $commerce->id,
        'user_id' => null,
    ])->first();

    $this->assertNotNull($purchase);

    
    $response->assertJson([
        'user' => [
            'id' => $user->id,
            
        ],
        'purchase' => [
            'amount' => ConversionHelper::moneyToPoints(100),
            
        ],
    ]);


	
    $responseContent = json_decode($response->getContent(), true);
    expect(isset($responseContent['url']))->toBeTrue();
    expect(str_contains($responseContent['url'], 'purchase/pay'))->toBeTrue();

	
    expect(str_contains($responseContent['url'], $purchase->uuid))->toBeTrue();
});

it('can pay a pre-created purchase', function () {
    $user = User::factory()->create(['points' => 500]);
    $commerce = Commerce::factory()->create(['percent' => 10]); 

    $response = $this->post(route('preCreatePurchase'), [
        'amount' => ConversionHelper::moneyToPoints(100),
        'userPass' => $user->pass,
        'commerceId' => $commerce->id,
    ]);

    $response->assertStatus(200);

    
    $purchase = Purchase::where([
        'amount' => ConversionHelper::moneyToPoints(100),
        'commerce_id' => $commerce->id,
        'user_id' => null,
    ])->first();

    $this->assertNotNull($purchase);

    
    $responseContent = json_decode($response->getContent(), true);
    $paymentUrl = $responseContent['url'];

    
    $paymentResponse = $this->actingAs($user)->get($paymentUrl);

    
    $paymentResponse->assertStatus(200);

    
    $purchase->refresh();
    $this->assertNotNull($purchase->paid_at);

	
    expect($user->fresh()->points)->toBe(750.0);
});

it('creates a record in purchase_user_points for each user receiving points', function () {
    
    $users = User::factory()->count(6)->create();
    for ($i = 0; $i < 5; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    
    $purchase->distributePoints();

    
    for ($i = 0; $i < 5; $i++) {
        $points = round($purchase->points * (0.25 / pow(2, $i)), 2); 
        $this->assertDatabaseHas('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $users[$i]->id,
            'points' => $points,
        ]);
    }
});

it('registers points for incomplete referral chain', function () {
    
    $users = User::factory()->count(4)->create();
    for ($i = 0; $i < 3; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    
    $purchase->distributePoints();

    
    for ($i = 0; $i < 3; $i++) {
        $points = round($purchase->points * (0.25 / pow(2, $i)), 2); 
        $this->assertDatabaseHas('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $users[$i]->id,
            'points' => $points,
        ]);
    }

    
    $this->assertDatabaseHas('purchase_user_points', [
        'purchase_id' => $purchase->id,
        'user_id' => $users[3]->id,
    ]);
});

it('verifies correct point totals for each user in purchase_user_points', function () {
    $users = User::factory()->count(6)->create();
    for ($i = 0; $i < 5; $i++) {
        $users[$i]->referrer()->associate($users[$i + 1]);
        $users[$i]->save();
    }

    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($users[0])->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    
    $purchase->distributePoints();

    
    for ($i = 0; $i < 5; $i++) {
        $expectedPoints = $purchase->points * (0.25 / pow(2, $i));
        $totalPoints = \DB::table('purchase_user_points')
            ->where('user_id', $users[$i]->id)
            ->sum('points');

        expect(abs($totalPoints - $expectedPoints))->toBeLessThan(0.01);

    }
});

it('ensures no points are registered for non-referral purchases', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create(['percent' => 10]);
    $purchase = Purchase::factory()->for($user)->for($commerce)->create(['amount' => ConversionHelper::moneyToPoints(100)]);

    
    $purchase->distributePoints();

    
    $this->assertDatabaseHas('purchase_user_points', [
        'purchase_id' => $purchase->id,
        'user_id' => $user->id,
    ]);

    
    $otherUsers = User::where('id', '!=', $user->id)->pluck('id');
    foreach ($otherUsers as $otherUserId) {
        $this->assertDatabaseMissing('purchase_user_points', [
            'purchase_id' => $purchase->id,
            'user_id' => $otherUserId,
        ]);
    }
});

