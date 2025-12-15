<?php

use App\Http\Controllers\Api\V1\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'throttle:api'])
    ->group(function () {
        Route::apiResource('/orders', OrderController::class)
            ->except(['update']);
    });
