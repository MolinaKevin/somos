<?php

use App\Models\Nro;
use App\Models\Somos;
use App\Models\Entity;

it('makes a contribution of the remaining amount after applying the entity percentage', function () {
    // Creamos una instancia de Somos
    $somos = Somos::factory()->create(['points' => 0]);

    // Creamos una Nro relacionada con la entidad y con Somos, y con un contributed_points de 1000
	$nro = Nro::factory()->create(['somos_id' => $somos->id, 'to_contribute' => 1000]);
    // Creamos una entidad con un porcentaje del 10%
    $entity = Entity::factory()->create(['percent' => 10]);
	$nro->entity()->save($entity);


    // Realizamos la contribución
    $nro->contribute();

    // Recargamos la instancia de Somos de la base de datos
    $somos->refresh();

    // Verificamos que la contribución se haya realizado correctamente. El monto de la contribución debe ser el 90% de 1000, es decir, 900.

    $this->assertDatabaseHas('contributions', [
		'somos_id' => $somos->id,
        'nro_id' => $nro->id,
		'amount' => 900
	]);

    $this->assertEquals(900, $somos->points);
    $this->assertEquals(900, $nro->contributed_points);
    $this->assertEquals(0, $nro->to_contribute);
});

it('makes no contribution if the donated_amount is 0', function () {
    // Creamos una instancia de Somos
    $somos = Somos::factory()->create(['points' => 0]);

    // Creamos una Nro relacionada con la entidad y con Somos, y con un contributed_points de 1000
	$nro = Nro::factory()->create(['somos_id' => $somos->id, 'to_contribute' => 0]);
    // Creamos una entidad con un porcentaje del 10%
    $entity = Entity::factory()->create(['percent' => 10]);
	$nro->entity()->save($entity);

    // Realizamos la contribución
    $nro->contribute();

    // Recargamos la instancia de Somos de la base de datos
    $somos->refresh();

    // Verificamos que no se haya realizado ninguna contribución
    $this->assertDatabaseHas('contributions', [
		'somos_id' => $somos->id,
        'nro_id' => $nro->id,
		'amount' => 0
	]);


    $this->assertEquals(0, $somos->points);
    $this->assertEquals(0, $nro->contributed_points);
    $this->assertEquals(0, $nro->to_contribute);
});
