<?php

namespace App\Enum;

enum UserGender: string {
    case Monsieur = 'man';
    case Madame = 'woman';
    case Autre = 'other';
}