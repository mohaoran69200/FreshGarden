<?php

namespace App\Enum;

enum UserGender: string {
    case Man = 'man';
    case Woman = 'woman';
    case Other = 'other';
}