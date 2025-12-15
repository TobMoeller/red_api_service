<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Orders\IndexRequest;
use App\Http\Requests\Api\V1\Orders\StoreRequest;
use App\Models\Order;
use App\Enums\Order\Status;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(IndexRequest $request): JsonResource
    {
        Gate::authorize('viewAny', Order::class);

        return Order::query()
            ->apiFilter($request)
            ->paginate(
                perPage: $request->validated('perPage', 10), // @phpstan-ignore argument.type
                page: $request->validated('page', 1), // @phpstan-ignore argument.type
            )
            ->toResourceCollection();
    }

    public function store(StoreRequest $request): JsonResource
    {
        Gate::authorize('create', Order::class);

        // TODO dispatch RED Provider Request

        return Order::create($request->validated())
            ->toResource();
    }

    public function show(Order $order): JsonResource
    {
        Gate::authorize('view', $order);

        return $order->toResource();
    }

    // public function update(Request $request, Order $order)
    // {
    //     //
    // }

    public function destroy(Order $order): HttpResponse
    {
        Gate::authorize('delete', $order);

        if ($order->status != Status::COMPLETED) {
            throw ValidationException::withMessages(['status' => __('Order can only be deleted when status is completed.')]);
        }

        $order->deleteOrFail();

        return Response::noContent();
    }
}
