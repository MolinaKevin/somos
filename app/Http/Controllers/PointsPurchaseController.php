<?php

namespace App\Http\Controllers;

use App\Models\PointsPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PointsPurchaseController extends Controller
{
    /**
     * Pay for a PointsPurchase.
     *
     * @param  Request  $request
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function pay(Request $request, $uuid)
    {
        
        $pointsPurchase = PointsPurchase::where('uuid', $uuid)->firstOrFail();

        
		$user = Auth::user();
        $commerce = $pointsPurchase->commerce;

        
        if ($user->points < $pointsPurchase->points) {
            
            return response()->json(['error' => 'Insufficient points'], 400);
        }

        
        $user->points -= $pointsPurchase->points;
        $user->save();

        
        $commerce->points += $pointsPurchase->points;
        $commerce->save();

        
        return response()->json(['success' => 'Points purchase paid successfully']);
    }
}

