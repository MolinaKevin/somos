<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commerce;
use App\Models\Cashout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCommerceCashoutController extends Controller
{
    public function index(Commerce $commerce, Request $request)
    {
        $user = Auth::user();

        if (!$commerce->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        
        $query = $commerce->cashouts();

        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        
        $perPage = $request->query('per_page', 15); 
        $cashouts = $query->paginate($perPage);

        return response()->json($cashouts, 200);
    }

    public function store(Request $request, Commerce $commerce)
    {
        $user = Auth::user();

        if (!$commerce->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'points' => 'required|numeric',
        ]);

        $cashout = new Cashout([
            'points' => $request->points,
            'commerce_id' => $commerce->id,
        ]);

        $commerce->cashouts()->save($cashout);

        return response()->json(['data' => $cashout], 201);
    }

    public function show(Commerce $commerce, Cashout $cashout)
    {
        $user = Auth::user();

        if ($cashout->commerce_id !== $commerce->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $cashout], 200);
    }

    public function update(Request $request, Commerce $commerce, Cashout $cashout)
    {
        $user = Auth::user();

        if ($cashout->commerce_id !== $commerce->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'points' => 'sometimes|numeric',
        ]);

        $cashout->update($request->only('points'));

        return response()->json(['data' => $cashout], 200);
    }

    public function destroy(Commerce $commerce, Cashout $cashout)
    {
        $user = Auth::user();

        if ($cashout->commerce_id !== $commerce->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $cashout->delete();

        return response()->json(null, 204);
    }
}

