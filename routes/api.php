<?php

use App\Http\Controllers\V1\AlbumController;
use App\Http\Controllers\V1\ImageManipulationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::middleware('auth:sanctum')->group(function(){

    // api routes
    Route::prefix('v1')->group(function(){
        Route::apiResource('album',AlbumController::class);
    
        Route::prefix('image')->group(function(){
            Route::get('/',[ImageManipulationController::class,'index']);
            Route::get('/by-album/{album}',[ImageManipulationController::class,'byAlbum']);
            Route::get('/{image}',[ImageManipulationController::class,'show']);
            Route::post('/resize',[ImageManipulationController::class,'resize']);
            Route::delete('/{image}',[ImageManipulationController::class,'destroy']);
        });
    });

});

