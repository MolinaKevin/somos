<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class PurchaseController extends Controller
{
	public function pay($uuid) {

		$user = Auth::user();

		// Si no hay ningún usuario autenticado, devuelve un error
		if (!$user) {
			return response()->json(['message' => 'Not authenticated.'], 401);
		}

		// Encuentra la compra por su UUID
		$purchase = Purchase::where('uuid', $uuid)->firstOrFail();

		// Asegúrate de que la compra no ha sido pagada aún
		if($purchase->isPaid()){
			return response()->json(['message' => 'Purchase already paid.'], 400);
		}

		$purchase->pay($user);

		// Retorna una respuesta exitosa
		return response()->json(['message' => 'Payment successful.']);
	}

	public function preCreate(Request $request)
	{
		// Valida los datos de entrada
		$data = $request->validate([
			'amount' => ['required', 'numeric', 'min:0'],
			'userPass' => ['required', 'exists:users,pass'],
			'commerceId' => ['required', 'exists:commerces,id'],
		]);

		// Crea una nueva compra sin asociarla a un usuario todavía
		$purchase = Purchase::create([
			'amount' => $data['amount'],
			'commerce_id' => $data['commerceId']
		]);

		// Genera una URL con el UUID de la compra
		$url = route('purchase.pay', ['uuid' => $purchase->uuid]);

		// Devuelve una respuesta con la información de la compra y la URL
		return response()->json([
			'user' => User::where('pass',$data['userPass'])->firstOrFail(),
			'purchase' => $purchase,
			'url' => $url,
		]);
	}

}
