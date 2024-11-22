<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Livewire\Livewire;
use App\Http\Controllers\PointsPurchaseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Admin\{
    CommerceController,
    NroController,
    UserController,
    SomosController,
    DonationController,
    ContributionController,
    CashoutController,
    PurchaseController as AdminPurchaseController,
    PointsPurchaseController as AdminPointsPurchaseController,
    FotoController,
    L10nController,
    CategoryController,
};



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

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('commerces', CommerceController::class);
        Route::resource('nros', NroController::class);
        Route::resource('clients', UserController::class);
        Route::resource('somos', SomosController::class);
        Route::resource('donations', DonationController::class);
        Route::resource('contributions', ContributionController::class);
        Route::resource('cashouts', CashoutController::class);
        Route::resource('purchases', AdminPurchaseController::class);
        Route::resource('pointsPurchases', AdminPointsPurchaseController::class);
        Route::resource('fotos', FotoController::class);
        Route::resource('l10ns', L10nController::class);
        Route::resource('categories', CategoryController::class);
        Route::get('categories/{category}/children', [CategoryController::class, 'children'])->name('categories.children');
        Route::get('categories/{category}/commerces', [CategoryController::class, 'commerces'])->name('categories.commerces');
    });
});



Route::get('pointsPurchase/pay/{uuid}', [PointsPurchaseController::class, 'pay'])->name('pointsPurchase.pay');
Route::post('/pre-create-purchase', [PurchaseController::class, 'preCreate'])->name('preCreatePurchase');
Route::post('purchase/pay/{uuid}', [PurchaseController::class, 'pay'])->name('purchase.pay');
Route::get('purchase/pay/{uuid}', [PurchaseController::class, 'pay'])->name('purchase.pay');

