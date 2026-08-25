<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingCartController;

Route::group(['prefix' => 'booking_cart', 'middleware' => ['auth:user-api', 'api.logger']], function (){
    Route::get('get',[BookingCartController::class, 'get']);
    Route::post('add',[BookingCartController::class, 'addToCart']);
    Route::post('update',[BookingCartController::class, 'updateCart']);
    Route::post('remove',[BookingCartController::class, 'removeCart']);
});
