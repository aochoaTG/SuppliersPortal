<?php

use App\Http\Controllers\EmployeeController;
use Illuminate\Support\Facades\Route;

Route::middleware('api.key')->group(function () {
    Route::post('/empleados/recibir',          [EmployeeController::class, 'recibir']);
    Route::post('/empleados/resolver-lideres', [EmployeeController::class, 'resolverLideresPendientes']);
});
