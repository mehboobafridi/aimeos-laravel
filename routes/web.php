<?php

use App\Models\ShippingAddress;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\Auth\UsersManagmentController;


Auth::routes();


Route::group(['middleware' => 'check.status'], function () {
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', [HomeController::class, 'home'])->name('home');
        Route::get('/home', [HomeController::class, 'home'])->name('home');
        Route::post('/connect-amazon', [HomeController::class, 'auth'])->name('connect_amazon');
        
        

      

        Route::group(['middleware' => ['permission:users-management']], function () {
            Route::resource('permissions', PermissionsController::class);
            Route::resource('users-management', UsersManagmentController::class);
        });

        


    });
});


Route::get('/token', function () {
    return csrf_token();
});


Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('optimize:clear');
    return redirect()->back()->with('hardReload', true);
});

