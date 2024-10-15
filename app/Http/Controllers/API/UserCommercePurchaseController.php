<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Commerce;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserCommercePurchaseController extends Controller
{
    public function index(Commerce $commerce, Request $request)
    {
        $user = Auth::user();

        if (!$commerce->users->contains($user->id)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Obtener los parámetros de fecha del request
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Construir la consulta
        $query = $commerce->purchases()->where('user_id', $user->id);

        // Aplicar el filtro por rango de fechas si está presente
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Paginación
        $perPage = $request->query('per_page', 15); // Default per_page to 15 if not provided
        $purchases = $query->paginate($perPage);

        return response()->json($purchases, 200);
    }
 

    public function store(Request $request, Commerce $commerce)
    {
        $user = Auth::user();
        $request->validate([
            'product_name' => 'required|string|max:255',
            'amount' => 'required|numeric',
            'purchase_date' => 'required|date',
        ]);

        $purchase = new Purchase([
            'user_id' => $user->id,
            'product_name' => $request->product_name,
            'amount' => $request->amount,
            'purchase_date' => $request->purchase_date,
        ]);

        $commerce->purchases()->save($purchase);

        return response()->json(['data' => $purchase], 201);
    }

    public function show(Commerce $commerce, Purchase $purchase)
    {
        $user = Auth::user();

        if ($purchase->user_id !== $user->id || $purchase->commerce_id !== $commerce->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json(['data' => $purchase], 200);
    }

    public function update(Request $request, Commerce $commerce, Purchase $purchase)
    {
        $user = Auth::user();

        if ($purchase->user_id !== $user->id || $purchase->commerce_id !== $commerce->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'product_name' => 'sometimes|string|max:255',
            'amount' => 'sometimes|numeric',
            'purchase_date' => 'sometimes|date',
        ]);

        $purchase->update($request->only('product_name', 'amount', 'purchase_date'));

        return response()->json(['data' => $purchase], 200);
    }

    public function destroy(Commerce $commerce, Purchase $purchase)
    {
        $user = Auth::user();

        if ($purchase->user_id !== $user->id || $purchase->commerce_id !== $commerce->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $purchase->delete();

        return response()->json(null, 204);
    }
}

