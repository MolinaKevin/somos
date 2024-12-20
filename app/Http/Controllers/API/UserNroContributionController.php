<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Nro;
use App\Models\Contribution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserNroContributionController extends Controller
{
    /**
     * Muestra una lista de contribuciones realizadas por la NRO autenticada.
     *
     * @param Nro $nro
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Nro $nro, Request $request)
    {
        $user = Auth::user();

        
        if (!$nro->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        
        $query = $nro->contributions();

        
        $perPage = $request->query('per_page', 15);
        $contributions = $query->paginate($perPage);

        return response()->json($contributions, 200);
    }

    /**
     * Muestra una contribución específica realizada por la NRO.
     *
     * @param Nro $nro
     * @param Contribution $contribution
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Nro $nro, Contribution $contribution)
    {
        $user = Auth::user();

        
        if ($contribution->nro_id !== $nro->id || !$nro->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $contribution], 200);
    }
}

