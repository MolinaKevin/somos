<?php

use App\Models\User;
use App\Models\Commerce;
use App\Models\Purchase;
use App\Models\Entity;
use Illuminate\Foundation\Testing\RefreshDatabase;


it('a user can have a commerce', function () {
    $user = User::factory()->create();
	$commerce = Commerce::factory()->create();
	$entity = Entity::factory()->make([]);
	$commerce->entity()->save($entity);

    // Attach the commerce to the user
    $user->commerces()->attach($commerce->id);

    // Check if the user has the commerce
    $this->assertTrue($user->commerces->contains($commerce));
});

it('a commerce can have multiple users', function () {
	$commerce = Commerce::factory()->create(); // Esto crea también una Entity
    $users = User::factory()->count(3)->create();

    // Attach the users to the commerce
    $commerce->users()->attach($users->pluck('id'));

    // Check if the commerce has all the users
    foreach ($users as $user) {
        $this->assertTrue($commerce->users->contains($user));
    }
});

it('a user can have multiple commerces', function () {
    $user = User::factory()->create();
	$commerces = Commerce::factory()->count(3)->create(); // Esto crea también una Entity para cada Commerce

    // Attach the commerces to the user
    $user->commerces()->attach($commerces->pluck('id'));

    // Check if the user has all the commerces
    foreach ($commerces as $commerce) {
        $this->assertTrue($user->commerces->contains($commerce));
    }
});

it('has a name', function () {
	$commerce = Commerce::factory()->create();
	$entity = Entity::factory()->make([
		'name' => 'Test Commerce',
	]);
	$commerce->entity()->save($entity);

	$commerce->load('entity');

    $this->assertEquals('Test Commerce', $commerce->name);
});

it('can be found by its name', function () {
	$commerce = Commerce::factory()->create();
	$entity = Entity::factory()->make([
		'name' => 'Test Commerce',
	]);
	$commerce->entity()->save($entity);


    $foundEntity = Entity::where('name', 'Test Commerce')->first();
	$foundCommerce = $foundEntity->entityable;

	$foundCommerce->load('entity');

    $this->assertInstanceOf(Commerce::class, $foundCommerce);
    $this->assertEquals('Test Commerce', $foundCommerce->name);
});


