<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

	public function give(Request $request)
	{
		// validación de los datos de entrada
		$validated = $request->validate([
			'receiver_id' => 'required|exists:users,id',
			'points' => 'required|integer|min:1',
		]);

		// obtén el usuario actualmente autenticado
		$giver = auth('api')->user();

		// Si el usuario no esta autenticado
		if (!$giver) {
			return response()->json([
                'error' => 'Not authenticated.'
            ], 401);
		}

		// asegúrate de que el usuario tiene suficientes puntos para dar
		if ($giver->points < $validated['points']) {
			return response()->json([
                'error' => 'Not enough points to give.'
            ], 402);
		}

		// obtén el usuario que recibirá los puntos
		$receiver = User::find($validated['receiver_id']);

		// transfiere los puntos
		DB::transaction(function () use ($giver, $receiver, $validated) {
			$giver->decrement('points', $validated['points']);
			$receiver->increment('points', $validated['points']);
		});

		// respuesta de éxito
		return response()->json(['message' => 'Points given successfully.']);
	}
	


}
