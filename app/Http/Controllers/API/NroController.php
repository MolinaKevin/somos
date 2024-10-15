<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Nro;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NroController extends Controller
{

    public function index()
    {
        // Obtener todos las nro 
        $nros = Nro::all();
        return response()->json(['data' => $nros]);
    }

    public function assignCategories(Request $request, Nro $nro) {
        $categoryIds = $request->input('categories');

        // Comprueba que las categorías existen
        $categories = Category::findMany($categoryIds);
        if (count($categories) !== count($categoryIds)) {
            return response()->json(['error' => 'Some categories do not exist'], 404);
        }

        // Asigna las categorías al comercio
        $nro->categories()->sync($categoryIds);

        return response()->json(['message' => 'Categories assigned successfully'], 200);
    }

    public function associateUser(Request $request, Nro $nro)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        
        $nro->users()->attach($user->id);

        return response()->json(['message' => 'User associated with nro successfully.'], 200);
    }

    public function activate(Nro $nro)
    {
        $nro->update(['active' => true]);
        return response()->json(['success' => true, 'message' => 'Nro activated successfully.']);
    }

    public function accept(Nro $nro)
    {
        $nro->update(['accepted' => true]);
        return response()->json(['success' => true, 'message' => 'Nro accepted successfully.']);
    }

    public function deactivate(Nro $nro)
    {
        $nro->update(['active' => false]);
        return response()->json(['success' => true, 'message' => 'Nro deactivated successfully.']);
    }

    public function unaccept(Nro $nro)
    {
        $nro->update([
            'accepted' => false,
            'active' => false, 
        ]);

        return response()->json(['success' => true, 'message' => 'Nro unaccepted and deactivated successfully.']);
    }

}
