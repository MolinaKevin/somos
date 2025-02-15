<?php

use App\Models\Commerce;
use App\Models\Cashout;
use App\Models\Nro;
use App\Services\DonationService;

it('gives all given points on cashout', function () {
    
    $commerce = Commerce::factory()->create(['gived_points' => 1000.0, 'donated_points' => 250.0]);

    
    $cashout = new Cashout();

    $cashout->setCommerce($commerce);

    $cashout->perform();

    
    expect($commerce->fresh()->gived_points)->toBe(0.0);
});

it('can create a cashout of donations if total points is almost equal to donated points', function () {
    $commerce = Commerce::factory()->create(['gived_points' => 100, 'donated_points' => 50]);

    $nros = Nro::factory()->count(3)->create();

    $donationData = [
        ['nro' => $nros[0], 'points' => 32.50, 'donated_points' => 16.25],
        ['nro' => $nros[1], 'points' => 33.50, 'donated_points' => 17.75],
        ['nro' => $nros[2], 'points' => 33.00, 'donated_points' => 16.50],
    ];

    $donationService = new DonationService();

    $cashout = $donationService->createDonationCashout($commerce, $donationData, false);

    
    $sumOfDonations = array_sum(array_column($donationData, 'points'));
    $this->assertTrue($commerce->gived_points - $sumOfDonations <= 1);

    
    $this->assertDatabaseHas('cashouts', [
        'id' => $cashout->id,
        'commerce_id' => $commerce->id,
        'points' => $sumOfDonations,
    ]);

    
    foreach ($donationData as $data) {
        $this->assertDatabaseHas('donations', [
            'commerce_id' => $commerce->id,
            'nro_id' => $data['nro']->id,
            'points' => $data['points'],
            'is_paid' => false,
            'cashout_id' => $cashout->id,
        ]);
    }
});

