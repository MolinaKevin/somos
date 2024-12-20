<?php

use App\Models\Nro;
use App\Models\Somos;

it('makes a contribution of the remaining points after applying the entity percentage', function () {
    
    $somos = Somos::factory()->create(['points' => 0]);

    
	$nro = Nro::factory()->create(['somos_id' => $somos->id, 'to_contribute' => 1000, 'percent' => 10]);

    
    $nro->contribute();

    
    $somos->refresh();

    

    $this->assertDatabaseHas('contributions', [
		'somos_id' => $somos->id,
        'nro_id' => $nro->id,
		'points' => 900
	]);

    $this->assertEquals(900, $somos->points);
    $this->assertEquals(900, $nro->contributed_points);
    $this->assertEquals(0, $nro->to_contribute);
});

it('makes no contribution if the donated_points is 0', function () {
    
    $somos = Somos::factory()->create(['points' => 0]);

    
	$nro = Nro::factory()->create(['somos_id' => $somos->id, 'to_contribute' => 0, 'percent' => 10]);

    
    $nro->contribute();

    
    $somos->refresh();

    
    $this->assertDatabaseHas('contributions', [
		'somos_id' => $somos->id,
        'nro_id' => $nro->id,
		'points' => 0
	]);


    $this->assertEquals(0, $somos->points);
    $this->assertEquals(0, $nro->contributed_points);
    $this->assertEquals(0, $nro->to_contribute);
});
