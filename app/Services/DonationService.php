<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Closure;
use App\Models\Commerce;
use App\Models\Nro;
use App\Exceptions\InsufficientDonatedPointsException;

class DonationService
{
    /**
     * Creates a donation
     *
     * @param Commerce $commerce
     * @param Nro $nro
     * @param float $amount
     * @param bool $isPaid
     * @return Donation
     * @throws InsufficientDonated_pointsException
     */
    public function createDonation(Commerce $commerce, Nro $nro, float $amount, bool $isPaid = false): Donation
    {
        if ($commerce->gived_points < $amount) {
            throw new InsufficientDonatedPointsException('The Commerce does not have enough gived points to create this donation');
        }

        $donation = new Donation([
            'commerce_id' => $commerce->id,
            'nro_id' => $nro->id,
            'amount' => $amount,
            'donated_amount' => $commerce->calculateDonation($amount),
            'is_paid' => $isPaid,
        ]);

        $donation->save();

        // Reduce donated_points from the commerce
        $commerce->gived_points -= $amount;
        $commerce->save();

        return $donation;
    }

    public function markAsPaid(Donation $donation): Donation
    {
        $donation->update(['is_paid' => true]);
        return $donation;
    }

    public function createDonationPackage(Commerce $commerce, array $donationData, bool $isPaid = false): array
    {
        // Calculate the total amount of the donation package
        $totalAmount = array_reduce($donationData, function ($carry, $item) {
            return $carry + $item['amount'];
        }, 0);

        // Check that the total amount of the donation package does not exceed the donated points
        if ($commerce->gived_points < $totalAmount) {
            throw new InsufficientDonatedPointsException('The total amount of the donation package exceeds the Commerce\'s gived points');
        }

        // Check that the total amount of the donation package does not leave more than 1 donated point
        if ($commerce->gived_points - $totalAmount > 1) {
            throw new ExcessiveDonatedPointsException('The total amount of the donation package leaves more than 1 donated point');
        }

        // Only create the donations if the total amount of the donation package leaves 1 or less donated points
        if ($commerce->gived_points - $totalAmount < 1) {
            // Create the donations
            $donations = [];
            foreach ($donationData as $data) {
                $donation = new Donation([
                    'commerce_id' => $commerce->id,
                    'nro_id' => $data['nro']->id,
                    'amount' => $data['amount'],
                    'donated_amount' => $commerce->calculateDonation($data['amount']),
                    'is_paid' => $isPaid,
                ]);
                $donation->save();

                $donations[] = $donation;
            }

            // Return the created donations
            return $donations;
        }

        // Return an empty array if no donations were created
        return [];
    }
    
    public function createDonationClosure(Commerce $commerce, array $donationData, bool $isPaid = false): Closure
    {
        // Calculate the total amount of the donation package
        $totalAmount = array_reduce($donationData, function ($carry, $item) {
            return $carry + $item['amount'];
        }, 0);

        // Check that the total amount of the donation package does not exceed the donated points
        if ($commerce->gived_points < $totalAmount) {
            throw new InsufficientDonatedPointsException('The total amount of the donation package exceeds the Commerce\'s gived points');
        }

        // Check that the total amount of the donation package does not leave more than 1 donated point
        if ($commerce->gived_points - $totalAmount > 1) {
            throw new ExcessiveDonatedPointsException('The total amount of the donation package leaves more than 1 donated point');
        }

        // Creamos un nuevo Closure y lo guardamos en la base de datos
        $closure = new Closure([
            'commerce_id' => $commerce->id,
            'amount' => $totalAmount,
        ]);
        
        $closure->setCommerce($commerce);

        $closure->save();

        $donations = [];

        foreach ($donationData as $data) {
            $donation = new Donation([
                'commerce_id' => $commerce->id,
                'nro_id' => $data['nro']->id,
                'amount' => $data['amount'],
                'donated_amount' => $data['donated_amount'],
                'is_paid' => $isPaid,
                'closure_id' => $closure->id,
            ]);

            $donation->save();

            $donations[] = $donation;
        }

        // Actualizamos los gived_points del comercio
        $commerce->gived_points -= $totalAmount;
        $commerce->save();

        return $closure;

    }

}

