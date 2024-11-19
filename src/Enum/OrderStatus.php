<?php

namespace App\Enum;

enum OrderStatus: string
{
    case EN_ATTENTE = 'En attente';
    case CONFIRMEE = 'Confirmée';
    case ANNULEE = 'Annulée';
}
