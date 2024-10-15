<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Cashout;
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
     * @param float $points
     * @param bool $isPaid
     * @return Donation
     * @throws InsufficientDonated_pointsException
     */
    public function createDonation(Commerce $commerce, Nro $nro, float $points, bool $isPaid = false): Donation
    {
        if ($commerce->gived_points < $points) {
            throw new InsufficientDonatedPointsException('The Commerce does not have enough gived points to create this donation');
        }

        $donation = new Donation([
            'commerce_id' => $commerce->id,
            'nro_id' => $nro->id,
            'points' => $points,
            'donated_points' => $commerce->calculateDonation($points),
            'is_paid' => $isPaid,
        ]);

        $donation->save();

        // Reduce donated_points from the commerce
        $commerce->gived_points -= $points;
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
        // Calculate the total points of the donation package
        $totalAmount = array_reduce($donationData, function ($carry, $item) {
            return $carry + $item['points'];
        }, 0);

        // Check that the total points of the donation package does not exceed the donated points
        if ($commerce->gived_points < $totalAmount) {
            throw new InsufficientDonatedPointsException('The total points of the donation package exceeds the Commerce\'s gived points');
        }

        // Check that the total points of the donation package does not leave more than 1 donated point
        if ($commerce->gived_points - $totalAmount > 1) {
            throw new ExcessiveDonatedPointsException('The total points of the donation package leaves more than 1 donated point');
        }

        // Only create the donations if the total points of the donation package leaves 1 or less donated points
        if ($commerce->gived_points - $totalAmount < 1) {
            // Create the donations
            $donations = [];
            foreach ($donationData as $data) {
                $donation = new Donation([
                    'commerce_id' => $commerce->id,
                    'nro_id' => $data['nro']->id,
                    'points' => $data['points'],
                    'donated_points' => $commerce->calculateDonation($data['points']),
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
    
    public function createDonationCashout(Commerce $commerce, array $donationData, bool $isPaid = false): Cashout
    {
        // Calculate the total points of the donation package
        $totalAmount = array_reduce($donationData, function ($carry, $item) {
            return $carry + $item['points'];
        }, 0);

        // Check that the total points of the donation package does not exceed the donated points
        if ($commerce->gived_points < $totalAmount) {
            throw new InsufficientDonatedPointsException('The total points of the donation package exceeds the Commerce\'s gived points');
        }

        // Check that the total points of the donation package does not leave more than 1 donated point
        if ($commerce->gived_points - $totalAmount > 1) {
            throw new ExcessiveDonatedPointsException('The total points of the donation package leaves more than 1 donated point');
        }

        // Creamos un nuevo Cashout y lo guardamos en la base de datos
        $cashout = new Cashout([
            'commerce_id' => $commerce->id,
            'points' => $totalAmount,
        ]);
        
        $cashout->setCommerce($commerce);

        $cashout->save();

        $donations = [];

        foreach ($donationData as $data) {
            $donation = new Donation([
                'commerce_id' => $commerce->id,
                'nro_id' => $data['nro']->id,
                'points' => $data['points'],
                'donated_points' => $data['donated_points'],
                'is_paid' => $isPaid,
                'cashout_id' => $cashout->id,
            ]);

            $donation->save();

            $donations[] = $donation;
        }

        // Actualizamos los gived_points del comercio
        $commerce->gived_points -= $totalAmount;
        $commerce->save();

        return $cashout;

    }

}

