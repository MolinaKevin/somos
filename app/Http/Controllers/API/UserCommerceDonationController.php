<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commerce;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCommerceDonationController extends Controller
{

    public function index(Commerce $commerce, Request $request)
    {
        $user = Auth::user();

        // Verificar que el usuario esté asociado con el comercio
        if (!$commerce->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Recuperar las donaciones asociadas al comercio
        $query = $commerce->donations();

        // Aplicar la paginación si es necesario
        $perPage = $request->query('per_page', 15);
        $donations = $query->paginate($perPage);

        return response()->json($donations, 200);
    }


    public function show(Commerce $commerce, Donation $donation)
    {
        $user = Auth::user();

        if ($donation->commerce_id !== $commerce->id || !$commerce->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $donation], 200);
    }
}

