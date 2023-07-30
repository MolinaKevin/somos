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
        // Buscar la compra de puntos por UUID
        $pointsPurchase = PointsPurchase::where('uuid', $uuid)->firstOrFail();

        // Obtener el usuario y el comercio asociados
		$user = Auth::user();
        $commerce = $pointsPurchase->commerce;

        // Verificar que el usuario tiene suficientes puntos
        if ($user->points < $pointsPurchase->points) {
            // No hay suficientes puntos, devolver un error
            return response()->json(['error' => 'Insufficient points'], 400);
        }

        // Descontar los puntos del usuario
        $user->points -= $pointsPurchase->points;
        $user->save();

        // Incrementar los puntos del comercio
        $commerce->points += $pointsPurchase->points;
        $commerce->save();

        // Devolver una respuesta de éxito
        return response()->json(['success' => 'Points purchase paid successfully']);
    }
}

