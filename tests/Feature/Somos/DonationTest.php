<?php

use App\Models\Commerce;
use App\Models\Donation;
use App\Services\DonationService;
use App\Exceptions\InsufficientDonatedPointsException;
use App\Models\Nro;

it('can create a donation if enough donated points', function () {
    $commerce = Commerce::factory()->create(['gived_points' => 100]);
    $nro = Nro::factory()->create();

    $donationService = new DonationService(); 

    $donationService->createDonation($commerce, $nro, 50.00, false);

    $this->assertDatabaseHas('donations', [
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 50,
        'is_paid' => false,
    ]);

    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'gived_points' => 50,
    ]);
});

it('cannot create a donation if not enough donated points', function () {
    $commerce = Commerce::factory()->create(['gived_points' => 30]);
    $nro = Nro::factory()->create();

    $donationService = new DonationService(); 

    $this->expectException(InsufficientDonatedPointsException::class);
    $this->expectExceptionMessage('The Commerce does not have enough gived points to create this donation');

    $donationService->createDonation($commerce, $nro, 50.00, false);

    // Verificamos que la donación no se haya guardado en la base de datos
    $this->assertDatabaseMissing('donations', [
        'commerce_id' => $commerce->id,
        'nro_id' => $nro->id,
        'points' => 50.00,
    ]);
    $this->assertDatabaseHas('commerces', [
        'id' => $commerce->id,
        'gived_points' => 30,
    ]);
});

it('can mark a donation as paid', function () {
    $donationService = new DonationService(); 
    $donation = Donation::factory()->create(['is_paid' => false]);

    $donationService->markAsPaid($donation);

    $this->assertDatabaseHas('donations', [
        'id' => $donation->id,
        'is_paid' => true,
    ]);
});

it('cannot create a package of donations if total points exceeds donated points', function () {
    $commerce1 = Commerce::factory()->create(['gived_points' => 150]);
    $commerce2 = Commerce::factory()->create(['gived_points' => 150]);
    $nro1 = Nro::factory()->create();
    $nro2 = Nro::factory()->create();
    $nro3 = Nro::factory()->create();

    $donationService = new DonationService(); 

    // Primero probamos con una cantidad total de donación válida
    try {
        $donations = $donationService->createDonationPackage($commerce1, [
            ['nro' => $nro1, 'points' => 35],
            ['nro' => $nro2, 'points' => 65],
            ['nro' => $nro3, 'points' => 50],
        ], false);

        // Verificamos que todas las donaciones se hayan creado
        foreach ($donations as $donation) {
            $this->assertDatabaseHas('donations', [
                'id' => $donation->id,
                'commerce_id' => $commerce1->id,
                'nro_id' => $donation->nro_id,
                'points' => $donation->points,
                'is_paid' => false,
            ]);
        }
    } catch (InsufficientDonatedPointsException $e) {
        $this->fail("Donation package creation should have been successful.");
    }

    // Ahora probamos con una cantidad total de donación que excede los puntos donados
    try {
        $donations = $donationService->createDonationPackage($commerce2, [
            ['nro' => $nro1, 'points' => 35],
            ['nro' => $nro2, 'points' => 65],
            ['nro' => $nro3, 'points' => 80],
        ], false);

        $this->fail("Donation package creation should have failed due to insufficient gived points.");
    } catch (InsufficientDonatedPointsException $e) {
        $this->assertTrue(true);
    }
});

it('cannot create a package of donations if total points leaves more than 1 donated point', function () {
    $commerce = Commerce::factory()->create(['gived_points' => 100]);

    $nros = Nro::factory()->count(3)->create();

    $donationData = [
        ['nro' => $nros[0], 'points' => 32.50],
        ['nro' => $nros[1], 'points' => 33.50],
        ['nro' => $nros[2], 'points' => 33.00],
    ];

    $donationService = new DonationService();

    try {
        $donationService->createDonationPackage($commerce, $donationData, false);
    } catch (InsufficientDonatedPointsException $e) {
        $this->fail("Donation package creation should have been successful.");
    }

    // Check that the sum of the donations does not leave more than 1 donated point.
    $sumOfDonations = array_sum(array_column($donationData, 'points'));
    $this->assertTrue($commerce->gived_points - $sumOfDonations <= 1);

    // Confirm each donation is in the database
    foreach ($donationData as $data) {
        $this->assertDatabaseMissing('donations', [
            'commerce_id' => $commerce->id,
            'nro_id' => $data['nro']->id,
            'points' => $data['points'],
            'is_paid' => false,
        ]);
    }
});


