<?php

namespace App\Enum;

enum NotificationType: string {
    case Message = 'message';
    case NEW_ORDERS = 'new orders';
    case NEW_PRODUCTS = 'new products';
    case FAVORITES = 'favorites';
}
