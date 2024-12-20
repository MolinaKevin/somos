<?php

use App\Models\User;
use App\Models\Nro;
use Illuminate\Foundation\Testing\RefreshDatabase;

it('a user can have a nro', function () {
    $user = User::factory()->create();
	$nro = Nro::factory()->create();

    
    $user->nros()->attach($nro->id);

    
    $this->assertTrue($user->nros->contains($nro));
});

it('a nro can have multiple users', function () {
	$nro = Nro::factory()->create();
    $users = User::factory()->count(3)->create();

    
    $nro->users()->attach($users->pluck('id'));

    
    foreach ($users as $user) {
        $this->assertTrue($nro->users->contains($user));
    }
});

it('a user can have multiple nros', function () {
    $user = User::factory()->create();
	$nros = Nro::factory()
		->count(3)
		->create();

    
    $user->nros()->attach($nros->pluck('id'));

    
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


