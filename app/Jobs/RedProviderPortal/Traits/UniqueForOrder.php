<?php

namespace App\Jobs\RedProviderPortal\Traits;

trait UniqueForOrder
{
    /** @var int */
    public $uniqueFor = 3600;

    public function uniqueId(): string
    {
        return $this->order->id;
    }
}

