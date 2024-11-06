<?php

namespace App\Enum;

enum PaymentMode: string {
    case PayPal = 'Paypal';
    case Carte_Bancaire = 'Carte bancaire';
    case Espèce = 'Espèce';
}