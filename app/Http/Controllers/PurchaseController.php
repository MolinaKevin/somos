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

		
		if (!$user) {
			return response()->json(['message' => 'Not authenticated.'], 401);
		}

		
		$purchase = Purchase::where('uuid', $uuid)->firstOrFail();

		
		if($purchase->isPaid()){
			return response()->json(['message' => 'Purchase already paid.'], 400);
		}

		$purchase->pay($user);

		
		return response()->json(['message' => 'Payment successful.']);
	}

	public function preCreate(Request $request)
	{
		
		$data = $request->validate([
			'amount' => ['required', 'numeric', 'min:0'],
			'userPass' => ['required', 'exists:users,pass'],
			'commerceId' => ['required', 'exists:commerces,id'],
		]);

		
		$purchase = Purchase::create([
			'amount' => $data['amount'],
			'commerce_id' => $data['commerceId']
		]);

		
		$url = route('purchase.pay', ['uuid' => $purchase->uuid]);

		
		return response()->json([
			'user' => User::where('pass',$data['userPass'])->firstOrFail(),
			'purchase' => $purchase,
			'url' => $url,
        ],
        200,
        [],
        JSON_PRESERVE_ZERO_FRACTION);
	}

}
