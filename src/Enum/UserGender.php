<?php

namespace App\Enum;

enum UserGender: string {
    case Monsieur = 'Homme';
    case Madame = 'Femme';
    case Autre = 'Autre';
}