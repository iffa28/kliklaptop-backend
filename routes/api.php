<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceByAdminController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\ServiceSparepartController;
use App\Http\Controllers\SparepartController;
use App\Http\Controllers\TransaksiPembelianController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/admin/product', [ProductController::class, 'index']);
    Route::post('admin/product/add-product', [ProductController::class, 'store']);
    Route::get('admin/product/{id}', [ProductController::class, 'edit']);
    Route::patch('admin/product/{id}/update', [ProductController::class, 'update']);
    Route::delete('admin/product/{id}/delete', [ProductController::class, 'destroy']);

    Route::get('/transaction', [TransaksiPembelianController::class, 'index']);
    Route::get('/transaction/{id}', [TransaksiPembelianController::class, 'show']);
    Route::patch('/transaction/{id}/update', [TransaksiPembelianController::class, 'update']);
    Route::delete('/transaction/{id}/delete', [TransaksiPembelianController::class, 'destroy']);

    Route::get('/sparepart', [SparepartController::class, 'index']);
    Route::post('/sparepart/add-sparepart', [SparepartController::class, 'store']);

    Route::get('/service-request', [ServiceRequestController::class, 'index']);

    Route::post('/service-request/servicebyadmin', [ServiceByAdminController::class, 'store']);

    Route::post('/service-request/service-sparepart', [ServiceSparepartController::class, 'store']);
    Route::post('/service-request/servicebyadmin/{id}/updated', [ServiceByAdminController::class, 'update']);
    Route::post('/service-spareparts/bulk', [ServiceSparepartController::class, 'bulkStore']);
});

Route::middleware(['auth:api'])->group(function () {


    Route::get('/service-request/servicebyadmin/{id}', [ServiceByAdminController::class, 'show']);



    Route::get('/service-request/servicebyadmin/by-service-id/{service_id}', [ServiceByAdminController::class, 'showByServiceId']);
});

// Untuk user yang sudah login
Route::middleware(['auth:api', 'role:customer'])->group(function () {
    Route::get('/home-user', [ServiceRequestController::class, 'homeUser']);
    Route::get('/service-request/active', [ServiceRequestController::class, 'listservice']);
    Route::get('/service-request/history/all', [ServiceRequestController::class, 'history']);
    Route::get('/service-request/{id}', [ServiceRequestController::class, 'edit']);

    Route::get('/service-request/service-sparepart/{id}', [ServiceSparepartController::class, 'getByServiceId']);


    Route::post('/service-request/create', [ServiceRequestController::class, 'store']);


    Route::get('/transaction-customer', [TransaksiPembelianController::class, 'index']);
    Route::get('/transaction-customer/active', [TransaksiPembelianController::class, 'listpembelian']);
    Route::get('/transaction-customer/history/all', [TransaksiPembelianController::class, 'history']);
    Route::post('/transaction/add-transaction', [TransaksiPembelianController::class, 'store']);





    Route::get('/product', [ProductController::class, 'index']);
});
