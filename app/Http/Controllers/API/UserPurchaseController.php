<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Purchase; // Asumiendo que tu modelo de compras es Purchase
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPurchaseController extends Controller
{
    /**
     * Display a listing of the purchases of the authenticated user.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = $user->purchases()->with(['commerce', 'pointsDistribution' => function ($query) use ($user) {
            $query->where('user_id', $user->id);
        }]);

        // Aplicar filtrado de fechas si están presentes en la solicitud
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        $purchases = $query->paginate($request->get('per_page', 15));

        // Transformar las compras para incluir `user_points_received`
        $purchases->getCollection()->transform(function ($purchase) use ($user) {
            $purchase->user_points_received = $purchase->pointsDistribution->where('user_id', $user->id)->sum('points');
            return $purchase;
        });

        return response()->json($purchases);
    }



    /**
     * Show the details of a specific purchase.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // Obtener la compra por ID
        $purchase = Purchase::findOrFail($id);

        // Agregar el nombre del comercio
        $purchase->commerce_name = $purchase->commerce->name;

        return response()->json($purchase);
    }

    /**
     * Eliminar una compra.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Obtener el usuario autenticado
        $user = Auth::user();

        // Buscar la compra por su ID y verificar que pertenece al usuario
        $purchase = Purchase::where('id', $id)->where('user_id', $user->id)->first();

        // Verificar si la compra existe y pertenece al usuario
        if (!$purchase) {
            return response()->json(['message' => 'Compra no encontrada o no pertenece al usuario'], 404);
        }

        // Eliminar la compra
        $purchase->delete();

        // Retornar una respuesta exitosa
        return response()->json(['message' => 'Compra eliminada exitosamente'], 200);
    }
}

