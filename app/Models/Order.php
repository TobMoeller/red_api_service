<?php

namespace App\Models;

use App\Order\Status;
use App\Order\Type;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    /** @use HasFactory<\Database\Factories\OrderFactory> */
    use HasFactory, HasUuids;

    public $guarded = [];

    protected function casts(): array
    {
        return [
            'type' => Type::class,
            'status' => Status::class,
        ];
    }
}
