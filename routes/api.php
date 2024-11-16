<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PointController;
use App\Http\Controllers\API\UserCommerceController;
use App\Http\Controllers\API\UserCommercePurchaseController;
use App\Http\Controllers\API\UserPurchaseController;
use App\Http\Controllers\API\UserPointsPurchaseController;
use App\Http\Controllers\API\UserCommerceCashoutController;
use App\Http\Controllers\API\UserCommerceDonationController;
use App\Http\Controllers\API\UserNroDonationController;
use App\Http\Controllers\API\UserNroContributionController;
use App\Http\Controllers\API\CommerceController;
use App\Http\Controllers\API\SomosController;
use App\Http\Controllers\API\NroController;
use App\Http\Controllers\API\UserNroController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\UsersController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ImageUploadController;
use App\Http\Controllers\API\AvatarUploadController;
use App\Http\Controllers\API\L10nController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\RegisterController;

/*
|--------------------------------------------------------------------------
| API Routes
|-----------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::apiResource('points', PointController::class);
Route::post('points/give', [PointController::class,'give']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);
Route::get('/commerces', [CommerceController::class, 'index']);
Route::get('/nros', [NroController::class, 'index']);
Route::get('/l10n/locales', [L10nController::class, 'availableLocales']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('user', [UserController::class, 'show']); 
    Route::put('user', [UserController::class, 'update']); 
    Route::get('/user/data', [UserController::class, 'data']);
    Route::get('/user/referral-points', [UserController::class, 'referralPurchasePoints']);
    Route::post('/user/upload-avatar', [UserController::class, 'uploadAvatar']);
    Route::apiResource('users', UsersController::class); 
    Route::apiResource('somos', SomosController::class)->parameters(['somos' => 'somos']);
    Route::apiResource('user/commerces', UserCommerceController::class);
    Route::apiResource('user/purchases', UserPurchaseController::class);
    Route::apiResource('user/point-purchases', UserPointsPurchaseController::class);
    Route::apiResource('user/nros', UserNroController::class);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('/user/commerces/{commerce}/purchases', UserCommercePurchaseController::class);
    Route::apiResource('/user/commerces/{commerce}/donations', UserCommerceDonationController::class);
    Route::apiResource('/user/commerces/{commerce}/cashouts', UserCommerceCashoutController::class);
    Route::apiResource('/user/nros/{nro}/donations', UserNroDonationController::class);
    Route::apiResource('/user/nros/{nro}/contributions', UserNroContributionController::class);
    Route::post('/commerces/{commerce}/categories', [CommerceController::class, 'assignCategories']);
    Route::post('/commerces/{commerce}/associate', [CommerceController::class, 'associateUser']);
    Route::post('/nros/{nro}/categories', [NroController::class, 'assignCategories']);
    Route::post('/nros/{nro}/associate', [NroController::class, 'associateUser']);
    Route::post('/commerces/{commerce}/accept', [CommerceController::class, 'accept']);
    Route::post('/commerces/{commerce}/unaccept', [CommerceController::class, 'unaccept']);
    Route::post('/user/commerces/{commerce}/activate', [CommerceController::class, 'activate']);
    Route::post('/user/commerces/{commerce}/deactivate', [CommerceController::class, 'deactivate']);
    Route::post('/nros/{nro}/accept', [NroController::class, 'accept']);
    Route::post('/nros/{nro}/unaccept', [NroController::class, 'unaccept']);
    Route::post('/user/nros/{nro}/activate', [NroController::class, 'activate']);
    Route::post('/user/nros/{nro}/deactivate', [NroController::class, 'deactivate']);
    Route::post('/commerces/{id}/upload-image', [ImageUploadController::class, 'uploadCommerceImage']);
    Route::post('/nros/{id}/upload-image', [ImageUploadController::class, 'uploadNroImage']);
    Route::post('/commerces/{id}/upload-avatar', [AvatarUploadController::class, 'uploadCommerceAvatar']);
    Route::post('/nros/{id}/upload-avatar', [AvatarUploadController::class, 'uploadNroAvatar']);
});


