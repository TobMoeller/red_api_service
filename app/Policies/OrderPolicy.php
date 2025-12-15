<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return true; // TODO only display user orders?

    }

    public function view(User $user, Order $order): bool
    {
        return true; // TODO check if it is the order of the user?
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        return true; // TODO check if it is the order of the user?

    }

    public function delete(User $user, Order $order): bool
    {
        return true; // TODO check if it is the order of the user?

    }
}
