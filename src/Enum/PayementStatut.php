<?php

namespace App\Enum;

enum PayementStatut: string {
    case EFFECTUE = 'effectué';
    case ENATTENTE = 'en attente';
    case REMBOURSE = 'remboursé';
}