<?php

namespace App\Enum;

enum UserGender: string {
    case MAN = 'man';
    case WOMAN = 'woman';
    case Other = 'other';
}