<?php

use App\Http\Controllers\BarcodeController;
use Illuminate\Support\Facades\Route;

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

Route::get('/ean13/{times}', 'App\Http\Controllers\BarcodeController@makeEan13Barcodes');
Route::post('/barcodes/download', [BarcodeController::class, 'downloadEan13Barcodes'])->name('barcodes-download');


