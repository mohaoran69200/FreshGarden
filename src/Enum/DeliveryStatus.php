<?php

namespace App\Enum;

enum DeliveryStatus: string {
    case En_Attente = "En attente";
    case Expediée = "Expediée";
    case Livrée = "Livrée";
    case Annulée = "Annulée";
}