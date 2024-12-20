<?php

use App\Models\User;
use App\Models\Role; 
use Laravel\Fortify\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('allows a user to define a referrer on registration', function () {
    $referrer = User::factory()->create();

    $user = User::factory()->create([
		'name' => 'test test',
        'referrer_pass' => $referrer->pass,
    ]);

	$createdUser = User::orderBy('id', 'desc')->first();

	//dd($user->toArray(), $createdUser->toArray());

    $this->assertDatabaseHas('users', [
		'id' => $createdUser->id,
		'points' => 0,
		'referrer_pass' => $referrer->pass,
    ]);


    $user->save();

    $this->assertEquals($referrer->pass, $user->referrer_pass);
});

it('does not allow a user to change their referrer pass after registration', function () {
    $referrer1 = User::factory()->create();
    $referrer2 = User::factory()->create();

    $user = User::factory()->create([
        'referrer_pass' => $referrer1->pass,
    ]);

    $user->referrer_pass = $referrer2->pass;

    
    $user->save();

    
    $user->refresh();

    $this->assertEquals($referrer1->pass, $user->referrer_pass);
});


it('requires that a referred user exists', function () {
    $user = User::factory()->make([
		'name' => 'Test user',
		'password' => 'secret',
        'referrer_pass' => 'DE-ZZZZZZZZZ',  
    ]);

    $response = $this->post(route('register'), $user->toArray());

    $response->assertSessionHasErrors('referrer_pass');
});

