<?php

namespace App\Enum;

enum PaymentMode: string {
    case PAYPAL = 'Paypal';
    case CB = 'Carte bancaire';
    case ESPECE = 'Espèce';
}