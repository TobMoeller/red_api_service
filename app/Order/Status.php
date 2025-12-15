<?php

namespace App\Order;

enum Status: string
{
    case ORDERED = 'ordered';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
}
