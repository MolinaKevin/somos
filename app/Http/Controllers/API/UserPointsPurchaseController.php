<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PointsPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPointsPurchaseController extends Controller
{
    /**
     * Display a listing of the point purchases of the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
        $user = Auth::user();

        
        $query = $user->pointsPurchases();

        
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        
        $perPage = $request->get('per_page', 15); 
        $pointsPurchases = $query->paginate($perPage);

        
        return response()->json($pointsPurchases);
    }

    /**
     * Show the details of a specific point purchase.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
        $pointsPurchase = PointsPurchase::findOrFail($id);

        return response()->json($pointsPurchase);
    }

    /**
     * Eliminar una compra de puntos.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        $user = Auth::user();

        
        $pointsPurchase = PointsPurchase::where('id', $id)->where('user_id', $user->id)->first();

        
        if (!$pointsPurchase) {
            return response()->json(['message' => 'Compra de puntos no encontrada o no pertenece al usuario'], 404);
        }

        
        $pointsPurchase->delete();

        
        return response()->json(['message' => 'Compra de puntos eliminada exitosamente'], 200);
    }
}

