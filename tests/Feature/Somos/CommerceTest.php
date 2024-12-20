<?php

use App\Models\User;
use App\Models\Commerce;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('a user can have a commerce', function () {
    $user = User::factory()->create();
    $commerce = Commerce::factory()->create();

    
    $user->commerces()->attach($commerce->id);

    
    $this->assertTrue($user->commerces->contains($commerce));
});

it('a commerce can have multiple users', function () {
    $commerce = Commerce::factory()->create(); 
    $users = User::factory()->count(3)->create();

    
    $commerce->users()->attach($users->pluck('id'));

    
    foreach ($users as $user) {
        $this->assertTrue($commerce->users->contains($user));
    }
});

it('a user can have multiple commerces', function () {
    $user = User::factory()->create();
    $commerces = Commerce::factory()->count(3)->create(); 

    
    $user->commerces()->attach($commerces->pluck('id'));

    
    foreach ($commerces as $commerce) {
        $this->assertTrue($user->commerces->contains($commerce));
    }
});

it('has a name', function () {
    $commerce = Commerce::factory()->create([
        'name' => 'Test Commerce',
    ]);

    $this->assertEquals('Test Commerce', $commerce->name);
});

it('can be found by its name', function () {
    $commerce = Commerce::factory()->create([
        'name' => 'Test Commerce',
    ]);

    $foundCommerce = Commerce::where('name', 'Test Commerce')->first();

    $this->assertInstanceOf(Commerce::class, $foundCommerce);
    $this->assertEquals('Test Commerce', $foundCommerce->name);
});

