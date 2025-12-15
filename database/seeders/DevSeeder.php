<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $user->tokens()->create([
            'name' => 'TestToken',
            'token' => hash('sha256', 'test'),
            'abilities' => ['*'],
            'expires_at' => null,
        ]);

        // User::factory(10)->create();

        Order::factory()->count(100)->create();
    }
}
