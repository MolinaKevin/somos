<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Nro;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNroDonationController extends Controller
{
    public function index(Nro $nro, Request $request)
    {
        $user = Auth::user();

        
        if (!$nro->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        
        $query = $nro->donations();

        
        $perPage = $request->query('per_page', 15);
        $donations = $query->paginate($perPage);

        return response()->json($donations, 200);
    }

    public function show(Nro $nro, Donation $donation)
    {
        $user = Auth::user();

        if ($donation->nro_id !== $nro->id || !$nro->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $donation], 200);
    }
}

