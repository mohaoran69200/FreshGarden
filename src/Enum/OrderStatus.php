<?php

namespace App\Enum;

enum OrderStatus: string {
    case En_attente = "En attente";
    case Confirmée = "Confirmé";
    case Annulée = "Annulée";
}