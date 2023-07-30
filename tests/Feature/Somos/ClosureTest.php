<?php

use App\Models\Commerce;
use App\Models\Closure;
use App\Models\Nro;
use App\Services\DonationService;

it('gives all given points on closure', function () {
    // Crea un comercio
    $commerce = Commerce::factory()->create(['gived_points' => 1000.0, 'donated_points' => 250.0]);

    // Realiza el cierre
    $closure = new Closure();

    $closure->setCommerce($commerce);

    $closure->perform();

    // Verifica que los puntos del comercio sean 0 después del cierre
    expect($commerce->fresh()->gived_points)->toBe(0.0);
});

it('can create a closure of donations if total amount is almost equal to donated points', function () {
    $commerce = Commerce::factory()->create(['gived_points' => 100, 'donated_points' => 50]);

    $nros = Nro::factory()->count(3)->create();

    $donationData = [
        ['nro' => $nros[0], 'amount' => 32.50, 'donated_amount' => 16.25],
        ['nro' => $nros[1], 'amount' => 33.50, 'donated_amount' => 17.75],
        ['nro' => $nros[2], 'amount' => 33.00, 'donated_amount' => 16.50],
    ];

    $donationService = new DonationService();

    $closure = $donationService->createDonationClosure($commerce, $donationData, false);

    // Check that the sum of the donations does not leave more than 1 donated point.
    $sumOfDonations = array_sum(array_column($donationData, 'amount'));
    $this->assertTrue($commerce->gived_points - $sumOfDonations <= 1);

    // Check the closure in the database
    $this->assertDatabaseHas('closures', [
        'id' => $closure->id,
        'commerce_id' => $commerce->id,
        'amount' => $sumOfDonations,
    ]);

    // Confirm each donation is in the database and associated with the closure
    foreach ($donationData as $data) {
        $this->assertDatabaseHas('donations', [
            'commerce_id' => $commerce->id,
            'nro_id' => $data['nro']->id,
            'amount' => $data['amount'],
            'is_paid' => false,
            'closure_id' => $closure->id,
        ]);
    }
});

