<?php

namespace App\Enum;

enum DeliveryMethod: string {
    case PICKUP = 'Retrait';
    case DELIVERY = 'Livraison';
}