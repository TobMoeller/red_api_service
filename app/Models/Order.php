<?php

namespace App\Models;

use App\Enums\Api\V1\Orders\SortOrders;
use App\Enums\Order\Status;
use App\Enums\Order\Type;
use App\Http\Requests\Api\V1\Orders\IndexRequest;
use App\Http\Resources\Api\V1\OrderResource;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\UseResource;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[UseResource(OrderResource::class)]
class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, HasUuids;

    public $guarded = [];

    protected $casts = [
        'type' => Type::class,
        'status' => Status::class,
    ];

    #[Scope]
    protected function apiFilter(Builder $query, IndexRequest $request): void
    {
        $query
            ->when(
                $name = $request->validated('name'),
                fn (Builder $query) => $query->where('name', 'like', '%'.$name.'%'),
            )
            ->when(
                ($sort = $request->validated('sort')) && ($sort = SortOrders::tryFrom($sort)),
                fn (Builder $query) => $query->customOrder($sort),
            );
    }

    #[Scope]
    protected function customOrder(Builder $query, SortOrders $sort): void
    {
        $query->orderBy($sort->field(), $sort->order());
    }
}
