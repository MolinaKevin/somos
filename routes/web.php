<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PointsPurchaseController;
use App\Http\Controllers\PurchaseController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});


Route::get('pointsPurchase/pay/{uuid}', [PointsPurchaseController::class, 'pay'])->name('pointsPurchase.pay');
Route::post('/pre-create-purchase', [PurchaseController::class, 'preCreate'])->name('preCreatePurchase');
Route::post('purchase/pay/{uuid}', [PurchaseController::class, 'pay'])->name('purchase.pay');
Route::get('purchase/pay/{uuid}', [PurchaseController::class, 'pay'])->name('purchase.pay');

