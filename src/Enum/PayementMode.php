<?php

namespace App\Enum;

enum PayementMode: string {
    case VIREMENT = 'virement';
    case PAYPAL = 'paypal';
    case CB = 'carte bancaire';
    case ESPECE = 'espece';
}