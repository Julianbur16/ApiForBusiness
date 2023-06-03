<?php

use App\Http\Controllers\BoxController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WhatsappController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('products',[ProductController::class, 'index']);
Route::post('products',[ProductController::class, 'store']);
Route::get('products/{product}',[ProductController::class, 'show']);
Route::put('products/{product}',[ProductController::class, 'update']);
Route::delete('products/{product}',[ProductController::class, 'destroy']);

Route::get('whatsapps',[WhatsappController::class, 'index']);
Route::post('whatsapps',[WhatsappController::class, 'store']);
Route::get('whatsapps/{whatsapp}',[WhatsappController::class, 'show']);
Route::put('whatsapps/{whatsapp}',[WhatsappController::class, 'update']);
Route::delete('whatsapps/{whatsapp}',[WhatsappController::class, 'destroy']);
Route::post('enviotemplate',[WhatsappController::class, 'sendtemplate']);

Route::post('/webhook', [WhatsappController::class, 'webhook']);
Route::get('/webhook', [WhatsappController::class, 'verify']);

Route::get('boxes',[BoxController::class, 'index']);
Route::get('boxes/{box}',[BoxController::class, 'show']);
Route::delete('boxes/{box}',[BoxController::class, 'destroy']);