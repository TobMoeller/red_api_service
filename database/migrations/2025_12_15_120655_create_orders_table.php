<?php

use App\Enums\Order\Status;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('type');
            $table->string('status')->default(Status::ORDERED->value);

            $table->timestamps();

            $table->index(['name', 'created_at']);
        });
    }
};
