<?php

namespace App\Enum;

enum OrderStatus: string {
    case ENATTENTE = "En attente";
    case CONFIRME = "Confirmé";
    case ANNULEE = "Annulée";
}