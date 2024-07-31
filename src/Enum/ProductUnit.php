<?php

namespace App\Enum;

enum ProductUnit: string {
    case Kg = 'kg';
    case Pièce = 'piece';

    public function toString(): string
    {
        return $this->value;
    }
}