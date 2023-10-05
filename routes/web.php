<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\Auth\UsersManagmentController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\OrdersController;

Auth::routes();


Route::group(['middleware' => 'check.status'], function () {
    Route::group(['middleware' => 'auth'], function () {

        Route::get('/', [HomeController::class, 'home'])->name('home');
        Route::get('/home', [HomeController::class, 'home'])->name('home');
        Route::get('/authCallback', [SubscriberController::class, 'amzCallBack'])->name('authCallback');
        Route::post('/connect-amazon', [SubscriberController::class, 'auth'])->name('connect_amazon');
        Route::get('/get-data', [SubscriberController::class,'getData'])->name('get.data');
        
        Route::delete('subscribed/{id}', [SubscriberController::class,'destroy'])->name('subscribed.destroy');

        
        Route::group(['middleware' => ['permission:users-management']], function () {
            Route::resource('permissions', PermissionsController::class);
            Route::resource('users-management', UsersManagmentController::class);
        });

        //show new orders
        // Route::group(['middleware' => ['permission:all-orders']], function () {
            Route::view('/new-orders', 'orders.new')->name('ViewNewOrders');
        // });

        //show canceled orders
        // Route::group(['middleware' => ['permission:all-orders']], function () {
            Route::view('/canceled-orders', 'orders.canceled')->name('ViewCanceledOrders');
        // });

        //show shipped orders
        // Route::group(['middleware' => ['permission:all-orders']], function () {
            Route::view('/shipped-orders', 'orders.shipped')->name('ViewShippedOrders');
        // });

        //load amazon orders to datatable on orders pages views
        Route::post('get_amazon_orders', [OrdersController::class, 'load_amazon_orders'])->name('load_amazon_orders');

        //load order-details
        Route::get('/get_order_details', [OrdersController::class, 'getOrderDetails'])->name('get_order_details');


        //mark order as shipped
        Route::post('/mark_order_shipped', [OrdersController::class, 'mark_order_shipped'])->name('mark_order_shipped');




        
    });
});

Route::get('/download_orders', [OrdersController::class, 'download_orders'])->name('download_orders');


Route::get('/token', function () {
    return csrf_token();
});


Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('optimize:clear');
    return redirect()->back()->with('hardReload', true);
});

// Route::get('/test', function(){
//     
// });