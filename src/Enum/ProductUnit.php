<?php

namespace App\Enum;

enum ProductUnit: string {
    case Kg = 'Kg';
    case Pièce = 'Piece';

    public function toString(): string
    {
        return $this->value;
    }
}