<?php

namespace App\Enum;

enum PaymentStatus: string
{
    case PAYE = 'Payé';
    case ENATTENTE = 'En attente';
    case REMBOURSE = 'Remboursé';
    case ECHOUE = "Echoué";
    case ANNULE = "Annulé";
}
