<?php

namespace App\Enum;

enum ProductUnit: string {
    case KG = 'kg';
    case PIECE = 'piece';

    public function toString(): string
    {
        return $this->value;
    }
}