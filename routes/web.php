<?php

use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AnalyticsController;

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
    return view('welcome');
});
// Route::get('uploads/images/{filename}', [ImageController::class, 'serve']);

Route::get('/success', [HomeController::class, 'success']);
Route::get('/cancel', [HomeController::class, 'cancel']);
