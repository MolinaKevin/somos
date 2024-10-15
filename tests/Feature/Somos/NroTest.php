<?php

use App\Models\User;
use App\Models\Nro;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('a user can have a nro', function () {
    $user = User::factory()->create();
	$nro = Nro::factory()->create();

    // Attach the nro to the user
    $user->nros()->attach($nro->id);

    // Check if the user has the nro 
    $this->assertTrue($user->nros->contains($nro));
});

it('a nro can have multiple users', function () {
	$nro = Nro::factory()->create();
    $users = User::factory()->count(3)->create();

    // Attach the users to the nro 
    $nro->users()->attach($users->pluck('id'));

    // Check if the nro has all the users
    foreach ($users as $user) {
        $this->assertTrue($nro->users->contains($user));
    }
});

it('a user can have multiple nros', function () {
    $user = User::factory()->create();
	$nros = Nro::factory()
		->count(3)
		->create();

    // Attach the nros to the user
    $user->nros()->attach($nros->pluck('id'));

    // Check if the user has all the nros
    foreach ($nros as $nro) {
        $this->assertTrue($user->nros->contains($nro));
    }
});

it('has a name', function () {
    $nro = Nro::factory()->create([
		'name' => 'Test Nro',
    ]);


    $this->assertEquals('Test Nro', $nro->name);
});

it('can be found by its name', function () {
    $nro = Nro::factory()->create([
		'name' => 'Test Nro',
    ]);

	$foundNro = Nro::where('name', 'Test Nro')->first();

    $this->assertInstanceOf(Nro::class, $foundNro);
    $this->assertEquals('Test Nro', $foundNro->name);

});


