<?php

namespace App\Enum;

enum DeliveryMode: string {
    case Retrait = 'Retrait';
    case Livraison = 'Livraison';
}