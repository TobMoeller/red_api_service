<?php

use App\Enums\Order\Type;
use App\Jobs\RedProviderPortal\CreateOrder;
use App\Models\Order;
use App\Services\RedProviderPortal\Contracts\RedProviderClient;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     $order = Order::first();
//     CreateOrder::dispatch($order);
//     dd($order);
//     // $client = app(RedProviderClient::class);
//     // dd($client->createOrder(Type::VPN_CONNECTION));
//     return 'TEST';
// });
