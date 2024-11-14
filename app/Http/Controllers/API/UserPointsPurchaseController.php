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
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Obtener las compras de puntos del usuario
        $query = $user->pointsPurchases();

        // Filtrar por fechas si están presentes en la solicitud
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Obtener las compras de puntos con paginación
        $perPage = $request->get('per_page', 15); // Establecer 15 como valor predeterminado
        $pointsPurchases = $query->paginate($perPage);

        // Retornar la respuesta con los resultados de la paginación
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
        // Obtener la compra de puntos por ID
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
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Buscar la compra de puntos por su ID
        $pointsPurchase = PointsPurchase::where('id', $id)->where('user_id', $user->id)->first();

        // Verificar si la compra de puntos existe y pertenece al usuario
        if (!$pointsPurchase) {
            return response()->json(['message' => 'Compra de puntos no encontrada o no pertenece al usuario'], 404);
        }

        // Eliminar la compra de puntos
        $pointsPurchase->delete();

        // Retornar una respuesta exitosa
        return response()->json(['message' => 'Compra de puntos eliminada exitosamente'], 200);
    }
}

