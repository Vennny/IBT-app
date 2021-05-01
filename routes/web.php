<?php

/**
 * Jméno, Město Analyzer
 * Bachelor's Thesis
 * @author Václav Trampeška
 */

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;

Route::group(['middleware' => 'auth'], function () {
    Route::get('/', [MainController::class, 'index']);
    Route::post('/', [MainController::class, 'handleRequest']);
});

Auth::routes();
