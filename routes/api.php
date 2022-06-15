<?php

use App\Http\Controllers\ApiController;
use App\Http\Controllers\FacebookController;
use App\Http\Controllers\SettingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Revolution\Line\Messaging\Bot;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/orders/payload/create', [ApiController::class, 'orderCreatePayload'])->name('api.orders.create');
Route::post('/orders/payload/update', [ApiController::class, 'orderUpdatePayload'])->name('api.orders.update');
Route::post('/orders/payload/delete', [ApiController::class, 'orderDeletePayload'])->name('api.orders.delete');

Route::post('/products/payload/create', [ApiController::class,'productsCreatePayload'])->name('api.products.create');
Route::post('/products/payload/update', [ApiController::class,'productsUpdatePayload'])->name('api.products.update');
Route::post('/products/payload/delete', [ApiController::class,'productsDeletePayload'])->name('api.products.delete');

Route::get('/dodochat/authenticate/{user}/{pass}', [ApiController::class, 'dodoChatAuthentication'])->name('dodochat.auth');
Route::get('/dodochat/logout/{authCode}', [SettingController::class, 'dodochat_users_logout'])->name('dodochat.logout');

Route::any('/facebook/webhook', [FacebookController::class, 'facebookWebhook'])->name('facebook.payload');
