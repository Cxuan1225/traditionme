<?php

declare(strict_types=1);

namespace App\Enums;

enum AdminViewMode: string
{
    case ADMIN = 'admin';
    case STOREFRONT = 'storefront';
}
