<?php

namespace App\Enums\Order;

enum Type: string
{
    case CONNECTOR = 'connector';
    case VPN_CONNECTION = 'vpn_connection';
}
