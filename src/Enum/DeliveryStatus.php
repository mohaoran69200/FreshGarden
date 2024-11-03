<?php

namespace App\Enum;

enum DeliveryStatus: string {
    case ENATTENTE = "En attente";
    case EXPEDIE = "Expediée";
    case LIVREE = "Livrée";
    case ANNULEE = "Annulée";
}