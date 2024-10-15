<?php

namespace App\Http\Controllers\API;

use App\Models\Category;
use App\Models\Commerce;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class CommerceController extends Controller
{

    public function index()
    {
        $commerces = Commerce::all();
        return response()->json(['data' => $commerces]);
    }

    public function assignCategories(Request $request, Commerce $commerce) {
        $categoryIds = $request->input('categories');

        // Comprueba que las categorías existen
        $categories = Category::findMany($categoryIds);
        if (count($categories) !== count($categoryIds)) {
            return response()->json(['error' => 'Some categories do not exist'], 404);
        }

        // Asigna las categorías al comercio
        $commerce->categories()->sync($categoryIds);

        return response()->json(['message' => 'Categories assigned successfully'], 200);
    }

    public function associateUser(Request $request, Commerce $commerce)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        
        $commerce->users()->attach($user->id);

        return response()->json(['message' => 'User associated with commerce successfully.'], 200);
    }

    public function activate(Commerce $commerce)
    {
        $commerce->update(['active' => true]);
        return response()->json(['success' => true, 'message' => 'Commerce activated successfully.']);
    }

    public function accept(Commerce $commerce)
    {
        $commerce->update(['accepted' => true]);
        return response()->json(['success' => true, 'message' => 'Commerce accepted successfully.']);
    }

    public function deactivate(Commerce $commerce)
    {
        $commerce->update(['active' => false]);
        return response()->json(['success' => true, 'message' => 'Commerce deactivated successfully.']);
    }

    public function unaccept(Commerce $commerce)
    {
        $commerce->update([
            'accepted' => false,
            'active' => false, 
        ]);

        return response()->json(['success' => true, 'message' => 'Commerce unaccepted and deactivated successfully.']);
    }


}
