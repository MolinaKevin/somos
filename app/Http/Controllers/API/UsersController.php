<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Devuelve todos los usuarios
        return User::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validar y crear un nuevo usuario
        $validator = Validator::make($request->all(),[
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'language' => 'nullable|string|max:2', // Asegúrate de incluir el idioma
            'pass' => 'nullable|string',
            'referrer_pass' => 'nullable|string',
            'current_team_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'language' => $validatedData['language'] ?? 'en', // Establece el idioma por defecto si no se proporciona
            'pass' => $validatedData['pass'],
            'referrer_pass' => $validatedData['referrer_pass'],
            'current_team_id' => $validatedData['current_team_id'],
        ]);

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */

    public function show(User $user)
    {
        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Validar y actualizar un usuario
        $validator = Validator::make($request->all(),[
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|string|min:8',
            'language' => 'sometimes|string|min:2|max:2', // Asegúrate de incluir el idioma
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        if (empty($validatedData['language'])) {
            $validatedData['language'] = 'en'; // Valor por defecto
        }

        $user->update($validatedData);

        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Elimina un usuario
        $user->delete();
        return response()->json(null, 204);
    }

    /**
     * Get the user's points and referral data.
     */
    public function data()
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Iniciamos el array de referidos
        $referrals = [];
        $lowLevelRefs = 0;

        // Contamos los referidos para cada nivel
        for ($level = 1; $level <= 7; $level++) {
            $referrals['level_' . $level] = $user->getReferralsCount($level);

            // Acumulamos los referidos de niveles 2 al 7 en lowlevelrefs
            if ($level >= 2) {
                $lowLevelRefs += $referrals['level_' . $level];
            }
        }

        return response()->json([
            'points' => $user->points,
            'referrals' => $referrals,
            'lowlevelrefs' => $lowLevelRefs,
        ]);
    }

    
}

